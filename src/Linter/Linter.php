<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Sniff\SniffLocator;
use Helmich\TypoScriptLint\Logging\LinterLoggerInterface;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Parser\ParserInterface;
use Helmich\TypoScriptParser\Tokenizer\Printer\CodeTokenPrinter;
use Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface;
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

    /** @var TokenPrinterInterface */
    private $tokenPrinter;

    public function __construct(
        TokenizerInterface $tokenizer,
        ParserInterface $parser,
        SniffLocator $sniffLocator,
        TokenPrinterInterface $tokenPrinter
    )
    {
        $this->tokenizer    = $tokenizer;
        $this->parser       = $parser;
        $this->sniffLocator = $sniffLocator;
        $this->tokenPrinter = $tokenPrinter;
    }

    /**
     * @param string                $filename
     * @param Report                $report
     * @param LinterConfiguration   $configuration
     * @param LinterLoggerInterface $logger
     * @return File
     */
    public function lintFile(string $filename, Report $report, LinterConfiguration $configuration, LinterLoggerInterface $logger): File
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            $file = new File($filename, "");
            $file->addIssue(new Issue(null, null, "file not readable", Issue::SEVERITY_ERROR, ""));

            return $file;
        }

        $file = new File($filename, $content);

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
    ): File {
        $sniffs = $this->sniffLocator->getTokenStreamSniffs($configuration);

        foreach ($sniffs as $sniff) {
            $sniffReport = $file->cloneEmpty();

            $logger->notifyFileSniffStart($file->getFilename(), get_class($sniff));
            $tokens = $sniff->sniff($tokens, $sniffReport, $configuration);

            $file = $file->merge($sniffReport);
            $logger->nofifyFileSniffComplete($file->getFilename(), get_class($sniff), $sniffReport);
        }

        $renderedFileContent = $this->tokenPrinter->printTokenStream($tokens);

        $file->setFixedContent($renderedFileContent);

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
    ): File {
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
