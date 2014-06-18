<?php
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



    /** @var \Helmich\TypoScriptLint\Linter\Report\File[] */
    private $files = [];



    /**
     * Adds a sub-report for a specific file.
     *
     * @param \Helmich\TypoScriptLint\Linter\Report\File $file The file sub-report.
     * @return void
     */
    public function addFile(File $file)
    {
        $this->files[] = $file;
    }



    /**
     * Returns all file reports.
     *
     * @return \Helmich\TypoScriptLint\Linter\Report\File[] All file reports.
     */
    public function getFiles()
    {
        return $this->files;
    }



}