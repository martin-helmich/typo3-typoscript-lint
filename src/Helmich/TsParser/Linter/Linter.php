<?php
namespace Helmich\TsParser\Linter;


use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Report\Report;
use Helmich\TsParser\Linter\Sniff\SniffLocator;
use Helmich\TsParser\Tokenizer\TokenizerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Linter implements LinterInterface
{



    /** @var \Helmich\TsParser\Tokenizer\TokenizerInterface */
    private $tokenizer;


    /** @var \Helmich\TsParser\Linter\Sniff\SniffLocator */
    private $sniffLocator;



    public function __construct(TokenizerInterface $tokenizer, SniffLocator $sniffLocator)
    {
        $this->tokenizer    = $tokenizer;
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
        $tokens = $this->tokenizer->tokenizeStream($filename);
        $this->lintTokenStream($tokens, $filename, $report, $configuration, $output);
    }



    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[]      $tokens
     * @param string                                            $filename
     * @param \Helmich\TsParser\Linter\Report\Report            $report
     * @param \Helmich\TsParser\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function lintTokenStream(array $tokens, $filename, Report $report, LinterConfiguration $configuration, OutputInterface $output)
    {
        $file   = new File($filename);
        $sniffs = $this->sniffLocator->getTokenStreamSniffs($configuration);

        foreach ($sniffs as $sniff)
        {
            $output->writeln('=> <info>Executing sniff <comment>' . get_class($sniff) . '</comment>.</info>');
            $sniff->sniff($tokens, $file, $configuration);
        }

        if (count($file->getWarnings()) > 0)
        {
            $report->addFile($file);
        }
    }
}