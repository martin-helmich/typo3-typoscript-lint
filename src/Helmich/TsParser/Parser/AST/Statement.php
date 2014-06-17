<?php
namespace Helmich\TsParser\Parser\AST;


/**
 * Abstract TypoScript statement.
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST
 */
abstract class Statement
{



    /**
     * The original source line. Useful for tracing and debugging.
     * @var int
     */
    public $sourceLine;



    /**
     * Base statement constructor.
     *
     * @param int $sourceLine The original source line.
     */
    public function __construct($sourceLine)
    {
        if (!$sourceLine > 0)
        {
            throw new \InvalidArgumentException(
                sprintf('Source line must be greater than 0 for %s statement (is: %d)!', get_class($this), $sourceLine)
            );
        }

        $this->sourceLine = $sourceLine;
    }


}