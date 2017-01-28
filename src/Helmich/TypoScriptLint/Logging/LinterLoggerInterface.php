<?php
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;

interface LinterLoggerInterface
{
    public function notifyFiles(array $files);

    public function notifyFileStart($filename);

    public function notifyFileSniffStart($filename, $sniffClass);

    public function nofifyFileSniffComplete($filename, $sniffClass, File $report);

    public function notifyFileComplete($filename, File $report);

    public function notifyRunComplete(Report $report);
}