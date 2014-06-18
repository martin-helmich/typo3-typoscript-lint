<?php
namespace Helmich\TsParser\Tokenizer;


/**
 * An exception that represents an error during tokenization.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TsParser
 * @subpackage Tokenizer
 */
class TokenizerException extends \Exception
{



    /** @var int */
    private $sourceLine;



    /**
     * Constructs a new tokenizer exception.
     *
     * @param string     $message    The message text.
     * @param int        $code       The exception code.
     * @param \Exception $previous   A nested previous exception.
     * @param int        $sourceLine The original source line.
     */
    public function __construct($message = "", $code = 0, \Exception $previous = NULL, $sourceLine = NULL)
    {
        parent::__construct($message, $code, $previous);

        $this->sourceLine = $sourceLine;
    }



    /**
     * Gets the original source line.
     *
     * @return int The original source line.
     */
    public function getSourceLine()
    {
        return $this->sourceLine;
    }



}