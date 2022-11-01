<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Logging\LinterLoggerInterface;

interface LinterInterface
{

    /**
     * @param string $filename
     * @param Report $report
     * @param LinterConfiguration $configuration
     * @param LinterLoggerInterface $logger
     *
     * @return File
     */
    public function lintFile(
        string $filename,
        Report $report,
        LinterConfiguration $configuration,
        LinterLoggerInterface $logger
    ): File;
}
