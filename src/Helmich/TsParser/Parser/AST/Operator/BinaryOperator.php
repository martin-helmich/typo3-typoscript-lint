<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\Statement;

abstract class BinaryOperator extends Statement
{



    /** @var \Helmich\TsParser\Parser\AST\ObjectPath */
    public $object;


}