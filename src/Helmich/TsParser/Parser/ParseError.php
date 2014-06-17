<?php
namespace Helmich\TsParser\Parser;


use Exception;

class ParseError extends \Exception
{



    /** @var int */
    private $sourceLine;



    public function __construct($message = "", $code = 0, $line = NULL, Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->sourceLine = $line;
    }



    public function getSourceLine()
    {
        return $this->sourceLine;
    }



}