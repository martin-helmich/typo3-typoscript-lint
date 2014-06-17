<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;

class Modification extends BinaryOperator
{



    /** @var \Helmich\TsParser\Parser\AST\Operator\ModificationCall */
    public $call;



    public function __construct(ObjectPath $object, ModificationCall $call)
    {
        $this->object = $object;
        $this->call   = $call;
    }

}