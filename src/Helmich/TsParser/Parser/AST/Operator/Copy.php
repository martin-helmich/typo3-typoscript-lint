<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;

class Copy extends BinaryOperator
{



    /** @var \Helmich\TsParser\Parser\AST\ObjectPath */
    public $target;



    function __construct(ObjectPath $object, ObjectPath $target)
    {
        $this->object = $object;
        $this->target = $target;
    }



}