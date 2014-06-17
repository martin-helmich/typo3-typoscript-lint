<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;


/**
 * A copy assignment.
 *
 * Example:
 *
 *     foo = bar
 *     baz < foo
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST\Operator
 */
class Copy extends BinaryOperator
{



    /**
     * The object path to copy the value from.
     * @var \Helmich\TsParser\Parser\AST\ObjectPath
     */
    public $target;



    /**
     * Constructs a copy statement.
     *
     * @param \Helmich\TsParser\Parser\AST\ObjectPath $object     The object to copy the value to.
     * @param \Helmich\TsParser\Parser\AST\ObjectPath $target     The object to copy the value from.
     * @param int                                     $sourceLine The original source line.
     */
    public function __construct(ObjectPath $object, ObjectPath $target, $sourceLine)
    {
        parent::__construct($sourceLine);

        $this->object = $object;
        $this->target = $target;
    }



}