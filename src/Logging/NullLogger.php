<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Logging;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;

/**
 * Special logger that does literally nothing
 *
 * Useful for unit testing.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Logging
 */
class NullLogger implements LinterLoggerInterface
{
    public function notifyFileNotFound(string $file): void
    {
    }

    public function notifyFiles(array $files): void
    {
    }

    public function notifyFileStart(string $filename): void
    {
    }

    public function notifyFileSniffStart(string $filename, string $sniffClass): void
    {
    }

    public function nofifyFileSniffComplete(string $filename, string $sniffClass, File $report): void
    {
    }

    public function notifyFileComplete(string $filename, File $report): void
    {
    }

    public function notifyRunComplete(Report $report): void
    {
    }
}
