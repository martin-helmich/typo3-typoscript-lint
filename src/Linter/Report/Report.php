<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Report;

/**
 * Checkstyle report for an entire set of files.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\Report
 */
class Report
{

    /** @var File[] */
    private $files = [];

    /**
     * Adds a sub-report for a specific file.
     *
     * @param File $file The file sub-report.
     *
     * @return void
     */
    public function addFile(File $file): void
    {
        $this->files[] = $file;
    }

    /**
     * Returns all file reports.
     *
     * @return File[] All file reports.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Returns the number of issues for the entire report.
     *
     * @return int The number of issues for the entire report.
     */
    public function countIssues(): int
    {
        $count = 0;
        foreach ($this->files as $file) {
            $count += count($file->getIssues());
        }
        return $count;
    }

    /**
     * Returns the number of issues with a given severity for the entire report
     *
     * @param string $severity The severity. Should be one of the Issue class' SEVERITY_* constants
     *
     * @return int The number of issues for the entire report.
     */
    public function countIssuesBySeverity(string $severity): int
    {
        $count = 0;
        foreach ($this->files as $file) {
            $count += count($file->getIssuesBySeverity($severity));
        }
        return $count;
    }
}
