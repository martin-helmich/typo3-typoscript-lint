<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;
use Helmich\TsParser\Parser\AST\Statement;

abstract class UnaryOperator extends Statement
{



    /** @var \Helmich\TsParser\Parser\AST\ObjectPath */
    public $object;



    public function __construct(ObjectPath $object)
    {
        $this->object = $object;
    }



}