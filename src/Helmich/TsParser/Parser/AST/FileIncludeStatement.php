<?php

namespace Helmich\TsParser\Parser\AST;


class FileIncludeStatement extends IncludeStatement
{



    /**
     * @var string
     */
    public $filename;



    public function __construct($filename)
    {
        $this->filename = $filename;
    }
} 