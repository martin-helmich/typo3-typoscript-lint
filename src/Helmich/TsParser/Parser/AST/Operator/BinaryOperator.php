<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\Statement;


/**
 * Abstract base class for statements with binary operators.
 *
 * @package    Helmich\TsParser
 * @subpcakage Parser\AST\Operator
 */
abstract class BinaryOperator extends Statement
{



    /**
     * The object on the left-hand side of the statement.
     *
     * @var \Helmich\TsParser\Parser\AST\ObjectPath
     */
    public $object;


}