<?php
namespace Helmich\TypoScriptLint\Linter;


use Helmich\TypoScriptLint\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

interface LinterInterface
{



    /**
     * @param                                                   $filename
     * @param \Helmich\TypoScriptLint\Linter\Report\Report            $report
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration      $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return
     */
    public function lintFile($filename, Report $report, LinterConfiguration $configuration, OutputInterface $output);


}