<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;
use Helmich\TsParser\Parser\AST\Statement;


/**
 * Abstract base class for statements with unary operators.
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST\Operator
 */
abstract class UnaryOperator extends Statement
{



    /**
     * The object the operator should be applied on.
     * @var \Helmich\TsParser\Parser\AST\ObjectPath
     */
    public $object;



    /**
     * Constructs a unary operator statement.
     *
     * @param \Helmich\TsParser\Parser\AST\ObjectPath $object     The object to operate on.
     * @param int                                     $sourceLine The original source line.
     */
    public function __construct(ObjectPath $object, $sourceLine)
    {
        parent::__construct($sourceLine);

        $this->object = $object;
    }



}