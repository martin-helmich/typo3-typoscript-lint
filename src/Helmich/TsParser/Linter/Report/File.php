<?php
namespace Helmich\TsParser\Linter\Report;


class File
{



    private $filename;


    /**
     * @var \Helmich\TsParser\Linter\Report\Warning[]
     */
    private $warnings = [];



    public function __construct($filename)
    {
        $this->filename = $filename;
    }



    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }



    /**
     * @param \Helmich\TsParser\Linter\Report\Warning $warning
     */
    public function addWarning(Warning $warning)
    {
        $this->warnings[] = $warning;
    }



    /**
     * @return \Helmich\TsParser\Linter\Report\Warning[]
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