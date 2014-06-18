<?php
namespace Helmich\TypoScriptLint\Linter;


use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptLint\Linter\Sniff\SniffLocator;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Parser\ParserInterface;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param string                                            $filename
     * @param \Helmich\TypoScriptLint\Linter\Report\Report            $report
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function lintFile($filename, Report $report, LinterConfiguration $configuration, OutputInterface $output)
    {
        $file = new File($filename);

        try
        {
            $tokens     = $this->tokenizer->tokenizeStream($filename);
            $statements = $this->parser->parseTokens($tokens);

            $this->lintTokenStream($tokens, $file, $configuration, $output);
            $this->lintSyntaxTree($statements, $file, $configuration, $output);
        }
        catch (TokenizerException $tokenizerException)
        {
            $file->addWarning(Warning::createFromTokenizerError($tokenizerException));
        }
        catch (ParseError $parseError)
        {
            $file->addWarning(Warning::createFromParseError($parseError));
        }

        if (count($file->getWarnings()) > 0)
        {
            $report->addFile($file);
        }
    }



    /**
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TypoScriptLint\Linter\Report\File           $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration   $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface    $output
     */
    private function lintTokenStream(array $tokens, File $file, LinterConfiguration $configuration, OutputInterface $output)
    {
        $sniffs = $this->sniffLocator->getTokenStreamSniffs($configuration);

        foreach ($sniffs as $sniff)
        {
            $output->writeln('=> <info>Executing sniff <comment>' . get_class($sniff) . '</comment>.</info>');
            $sniff->sniff($tokens, $file, $configuration);
        }
    }



    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement[]   $statements
     * @param \Helmich\TypoScriptLint\Linter\Report\File         $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface  $output
     */
    private function lintSyntaxTree(array $statements, File $file, LinterConfiguration $configuration, OutputInterface $output)
    {
        $sniffs = $this->sniffLocator->getSyntaxTreeSniffs($configuration);

        foreach ($sniffs as $sniff)
        {
            $output->writeln('=> <info>Executing sniff <comment>' . get_class($sniff) . '</comment>.</info>');
            $sniff->sniff($statements, $file, $configuration);
        }
    }
}