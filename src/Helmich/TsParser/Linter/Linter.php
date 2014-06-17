<?php
namespace Helmich\TsParser\Linter;


use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Report\Report;
use Helmich\TsParser\Linter\Sniff\SniffLocator;
use Helmich\TsParser\Parser\ParserInterface;
use Helmich\TsParser\Tokenizer\TokenizerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Linter implements LinterInterface
{



    /** @var \Helmich\TsParser\Tokenizer\TokenizerInterface */
    private $tokenizer;


    /** @var \Helmich\TsParser\Parser\ParserInterface */
    private $parser;


    /** @var \Helmich\TsParser\Linter\Sniff\SniffLocator */
    private $sniffLocator;



    public function __construct(TokenizerInterface $tokenizer, ParserInterface $parser, SniffLocator $sniffLocator)
    {
        $this->tokenizer    = $tokenizer;
        $this->parser       = $parser;
        $this->sniffLocator = $sniffLocator;
    }



    /**
     * @param string                                            $filename
     * @param \Helmich\TsParser\Linter\Report\Report            $report
     * @param \Helmich\TsParser\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function lintFile($filename, Report $report, LinterConfiguration $configuration, OutputInterface $output)
    {
        $tokens     = $this->tokenizer->tokenizeStream($filename);
        $statements = $this->parser->parseTokens($tokens);

        $file = new File($filename);

        $this->lintTokenStream($tokens, $file, $configuration, $output);
        $this->lintSyntaxTree($statements, $file, $configuration, $output);

        if (count($file->getWarnings()) > 0)
        {
            $report->addFile($file);
        }
    }



    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[]      $tokens
     * @param \Helmich\TsParser\Linter\Report\File              $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
     * @param \Helmich\TsParser\Parser\AST\Statement[]          $statements
     * @param \Helmich\TsParser\Linter\Report\File              $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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