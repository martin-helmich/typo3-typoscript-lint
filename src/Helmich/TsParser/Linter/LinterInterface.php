<?php
namespace Helmich\TsParser\Linter;


use Helmich\TsParser\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

interface LinterInterface
{



    /**
     * @param                                                   $filename
     * @param \Helmich\TsParser\Linter\Report\Report            $report
     * @param \Helmich\TsParser\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return
     */
    public function lintFile($filename, Report $report, LinterConfiguration $configuration, OutputInterface $output);


}