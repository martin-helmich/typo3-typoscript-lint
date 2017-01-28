<?php
namespace Helmich\TypoScriptLint\Linter;

use Helmich\TypoScriptLint\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

interface LinterInterface
{

    /**
     * @param string              $filename
     * @param Report              $report
     * @param LinterConfiguration $configuration
     * @param OutputInterface     $output
     * @return
     */
    public function lintFile($filename, Report $report, LinterConfiguration $configuration, OutputInterface $output);
}
