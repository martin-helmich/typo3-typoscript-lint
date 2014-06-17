<?php
namespace Helmich\TsParser\Parser\AST\Operator;


class ModificationCall {

    public $method;
    public $arguments;



    /**
     * @param string $method
     * @param string $arguments
     */
    public function __construct($method, $arguments)
    {
        $this->arguments = $arguments;
        $this->method    = $method;
    }


} 