<?php
namespace Helmich\TypoScriptLint\Linter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Sniff\SniffLocator;
use Helmich\TypoScriptLint\Logging\LinterLoggerInterface;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Parser\ParserInterface;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;

class Linter implements LinterInterface
{

    /** @var TokenizerInterface */
    private $tokenizer;

    /** @var ParserInterface */
    private $parser;

    /** @var SniffLocator */
    private $sniffLocator;

    public function __construct(TokenizerInterface $tokenizer, ParserInterface $parser, SniffLocator $sniffLocator)
    {
        $this->tokenizer    = $tokenizer;
        $this->parser       = $parser;
        $this->sniffLocator = $sniffLocator;
    }

    /**
     * @param string                $filename
     * @param Report                $report
     * @param LinterConfiguration   $configuration
     * @param LinterLoggerInterface $logger
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
            $file->addIssue(Issue::createFromTokenizerError($tokenizerException));
        } catch (ParseError $parseError) {
            $file->addIssue(Issue::createFromParseError($parseError));
        }

        if (count($file->getIssues()) > 0) {
            $report->addFile($file);
        }

        return $file;
    }

    /**
     * @param TokenInterface[]      $tokens
     * @param File                  $file
     * @param LinterConfiguration   $configuration
     * @param LinterLoggerInterface $logger
     * @return File
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
     * @param Statement[]           $statements
     * @param File                  $file
     * @param LinterConfiguration   $configuration
     * @param LinterLoggerInterface $logger
     * @return File
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
