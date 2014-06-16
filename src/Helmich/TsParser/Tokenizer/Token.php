<?php
namespace Helmich\TsParser\Tokenizer;


class Token implements TokenInterface
{



    /** @var string */
    private $type;


    /** @var string */
    private $value;


    /** @var int */
    private $line;



    /**
     * @param string $type
     * @param string $value
     * @param int    $line
     */
    public function __construct($type, $value, $line)
    {
        $this->type  = $type;
        $this->value = $value;
        $this->line  = $line;
    }



    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }



    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }



    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }
}