<?php
namespace Helmich\TsParser\Parser\AST;


class Scalar
{



    public $value;



    public function __construct($value)
    {
        $this->value = $value;
    }



}