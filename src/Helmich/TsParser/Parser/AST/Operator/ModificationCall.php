<?php
namespace Helmich\TsParser\Parser\AST\Operator;


/**
 * A modification call (usually on the right-hand side of a modification statement).
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST\Operator
 */
class ModificationCall
{



    /**
     * The method name.
     * @var string
     */
    public $method;


    /**
     * The argument list.
     * @var string
     */
    public $arguments;



    /**
     * Modification call constructor.
     *
     * @param string $method    The method name.
     * @param string $arguments The argument list.
     */
    public function __construct($method, $arguments)
    {
        $this->arguments = $arguments;
        $this->method    = $method;
    }


} 