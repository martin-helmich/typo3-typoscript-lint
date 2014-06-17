<?php
namespace Helmich\TsParser\Parser\AST;


class ObjectPath
{



    public $relativeName;


    public $absoluteName;



    public function __construct($absoluteName, $relativeName)
    {
        $this->absoluteName = $absoluteName;
        $this->relativeName = $relativeName;
    }



}