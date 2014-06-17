<?php
namespace Helmich\TsParser\Linter\Report;


/**
 * Checkstyle report containing warnings for a single TypoScript file.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TsParser
 * @subpackage Linter\Report
 */
class File
{



    /** @var string */
    private $filename;


    /** @var \Helmich\TsParser\Linter\Report\Warning[] */
    private $warnings = [];



    /**
     * Constructs a new file report.
     *
     * @param string $filename The filename.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }



    /**
     * Gets the filename.
     *
     * @return string The filename.
     */
    public function getFilename()
    {
        return $this->filename;
    }



    /**
     * Adds a new warning for this file.
     *
     * @param \Helmich\TsParser\Linter\Report\Warning $warning The new warning.
     * @return void
     */
    public function addWarning(Warning $warning)
    {
        $this->warnings[] = $warning;
    }



    /**
     * Gets all warnings for this file. The warnings will be sorted by line
     * numbers, not by order of addition to this report.
     *
     * @return \Helmich\TsParser\Linter\Report\Warning[] The warnings for this file.
     */
    public function getWarnings()
    {
        usort(
            $this->warnings,
            function (Warning $a, Warning $b) { return $a->getLine() - $b->getLine(); }
        );
        return $this->warnings;
    }



}