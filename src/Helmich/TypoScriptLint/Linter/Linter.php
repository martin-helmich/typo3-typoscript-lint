<?php
namespace Helmich\TypoScriptLint\Linter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptLint\Linter\Sniff\SniffLocator;
use Helmich\TypoScriptLint\Logging\LinterLoggerInterface;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Parser\ParserInterface;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;

class Linter implements LinterInterface
{

    /** @var \Helmich\TypoScriptParser\Tokenizer\TokenizerInterface */
    private $tokenizer;

    /** @var \Helmich\TypoScriptParser\Parser\ParserInterface */
    private $parser;

    /** @var \Helmich\TypoScriptLint\Linter\Sniff\SniffLocator */
    private $sniffLocator;

    public function __construct(TokenizerInterface $tokenizer, ParserInterface $parser, SniffLocator $sniffLocator)
    {
        $this->tokenizer    = $tokenizer;
        $this->parser       = $parser;
        $this->sniffLocator = $sniffLocator;
    }

    /**
     * @param string                                             $filename
     * @param \Helmich\TypoScriptLint\Linter\Report\Report       $report
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration $configuration
     * @param LinterLoggerInterface                              $logger
     * @return File
     */
    public function lintFile($filename, Report $report, LinterConfiguration $configuration, LinterLoggerInterface $logger)
    {
        $file = new File($filename);

        try {
            $tokens     = $this->tokenizer->tokenizeStream($filename);
            $statements = $this->parser->parseTokens($tokens);

            $file = $this->lintTokenStream($tokens, $file, $configuration, $logger);
            $file = $this->lintSyntaxTree($statements, $file, $configuration, $logger);
        } catch (TokenizerException $tokenizerException) {
            $file->addWarning(Warning::createFromTokenizerError($tokenizerException));
        } catch (ParseError $parseError) {
            $file->addWarning(Warning::createFromParseError($parseError));
        }

        if (count($file->getWarnings()) > 0) {
            $report->addFile($file);
        }

        return $file;
    }

    /**
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TypoScriptLint\Linter\Report\File           $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration   $configuration
     * @param LinterLoggerInterface                                $logger
     */
    private function lintTokenStream(
        array $tokens,
        File $file,
        LinterConfiguration $configuration,
        LinterLoggerInterface $logger
    ) {
        $sniffs = $this->sniffLocator->getTokenStreamSniffs($configuration);

        foreach ($sniffs as $sniff) {
            $sniffReport = $file->cloneEmpty();

            $logger->notifyFileSniffStart($file->getFilename(), get_class($sniff));
            $sniff->sniff($tokens, $sniffReport, $configuration);

            $file = $file->merge($sniffReport);
            $logger->nofifyFileSniffComplete($file->getFilename(), get_class($sniff), $sniffReport);
        }

        return $file;
    }

    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement[]   $statements
     * @param \Helmich\TypoScriptLint\Linter\Report\File         $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration $configuration
     * @param LinterLoggerInterface                              $logger
     */
    private function lintSyntaxTree(
        array $statements,
        File $file,
        LinterConfiguration $configuration,
        LinterLoggerInterface $logger
    ) {
        $sniffs = $this->sniffLocator->getSyntaxTreeSniffs($configuration);

        foreach ($sniffs as $sniff) {
            $sniffReport = $file->cloneEmpty();

            $logger->notifyFileSniffStart($file->getFilename(), get_class($sniff));
            $sniff->sniff($statements, $sniffReport, $configuration);

            $file = $file->merge($sniffReport);
            $logger->nofifyFileSniffComplete($file->getFilename(), get_class($sniff), $sniffReport);
        }

        return $file;
    }
}
