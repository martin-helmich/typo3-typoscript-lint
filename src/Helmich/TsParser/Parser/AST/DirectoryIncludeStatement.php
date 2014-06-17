<?php
namespace Helmich\TsParser\Parser\AST;


class DirectoryIncludeStatement extends IncludeStatement
{



    /** @var string */
    public $directory;


    /** @var string */
    public $extension;



    public function __construct($directory, $extension = NULL)
    {
        $this->directory = $directory;
        $this->extension = $extension;
    }


} 