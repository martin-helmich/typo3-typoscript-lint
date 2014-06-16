<?php
namespace Helmich\TsParser\Linter\Report;



class Report
{



    /** @var \Helmich\TsParser\Linter\Report\File[] */
    private $files = [];



    /**
     * @param \Helmich\TsParser\Linter\Report\File $file
     */
    public function addFile(File $file)
    {
        $this->files[] = $file;
    }



    /**
     * @return \Helmich\TsParser\Linter\Report\File[]
     */
    public function getFiles()
    {
        return $this->files;
    }



}