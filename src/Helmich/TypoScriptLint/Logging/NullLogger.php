<?php
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;

class NullLogger implements LinterLoggerInterface
{

    public function notifyFiles(array $files)
    {
        // TODO: Implement notifyFiles() method.
    }

    public function notifyFileStart($filename)
    {
        // TODO: Implement notifyFileStart() method.
    }

    public function notifyFileSniffStart($filename, $sniffClass)
    {
        // TODO: Implement notifyFileSniffStart() method.
    }

    public function nofifyFileSniffComplete($filename, $sniffClass, File $report)
    {
        // TODO: Implement nofifyFileSniffComplete() method.
    }

    public function notifyFileComplete($filename, File $report)
    {
        // TODO: Implement notifyFileComplete() method.
    }

    public function notifyRunComplete(Report $report)
    {
        // TODO: Implement notifyRunComplete() method.
    }
}