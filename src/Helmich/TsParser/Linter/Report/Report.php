<?php
namespace Helmich\TsParser\Linter\Report;


/**
 * Checkstyle report for an entire set of files.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TsParser
 * @subpackage Linter\Report
 */
class Report
{



    /** @var \Helmich\TsParser\Linter\Report\File[] */
    private $files = [];



    /**
     * Adds a sub-report for a specific file.
     *
     * @param \Helmich\TsParser\Linter\Report\File $file The file sub-report.
     * @return void
     */
    public function addFile(File $file)
    {
        $this->files[] = $file;
    }



    /**
     * Returns all file reports.
     *
     * @return \Helmich\TsParser\Linter\Report\File[] All file reports.
     */
    public function getFiles()
    {
        return $this->files;
    }



}