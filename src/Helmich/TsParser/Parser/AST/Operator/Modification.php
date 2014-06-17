<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;


/**
 * A modification statement.
 *
 * Example:
 *
 *     foo  = bar
 *     foo := appendToString(baz)
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST\Operator
 */
class Modification extends BinaryOperator
{



    /**
     * The modification call.
     * @var \Helmich\TsParser\Parser\AST\Operator\ModificationCall
     */
    public $call;



    /**
     * Constructs a modification statement.
     *
     * @param \Helmich\TsParser\Parser\AST\ObjectPath                $object     The object to modify.
     * @param \Helmich\TsParser\Parser\AST\Operator\ModificationCall $call       The modification call.
     * @param int                                                    $sourceLine The original source line.
     */
    public function __construct(ObjectPath $object, ModificationCall $call, $sourceLine)
    {
        parent::__construct($sourceLine);

        $this->object = $object;
        $this->call   = $call;
    }

}