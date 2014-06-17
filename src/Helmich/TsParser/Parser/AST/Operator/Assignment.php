<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;
use Helmich\TsParser\Parser\AST\Scalar;

class Assignment extends BinaryOperator
{



    public $value;



    public function __construct(ObjectPath $object, Scalar $value)
    {
        $this->object = $object;
        $this->value  = $value;
    }

}