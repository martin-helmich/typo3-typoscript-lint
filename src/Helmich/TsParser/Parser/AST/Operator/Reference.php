<?php
namespace Helmich\TsParser\Parser\AST\Operator;


use Helmich\TsParser\Parser\AST\ObjectPath;


/**
 * A reference statement.
 *
 * Example:
 *
 *     foo = bar
 *     baz <= foo
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST\Operator
 */
class Reference extends BinaryOperator
{



    /**
     * The target object to reference to.
     * @var \Helmich\TsParser\Parser\AST\ObjectPath
     */
    public $target;



    /**
     * Constructs a new reference statement.
     *
     * @param \Helmich\TsParser\Parser\AST\ObjectPath $object     The reference object.
     * @param \Helmich\TsParser\Parser\AST\ObjectPath $target     The target object.
     * @param int                                     $sourceLine The original source line.
     */
    public function __construct(ObjectPath $object, ObjectPath $target, $sourceLine)
    {
        parent::__construct($sourceLine);

        $this->object = $object;
        $this->target = $target;
    }


} 