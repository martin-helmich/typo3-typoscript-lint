<?php

namespace Helmich\TsParser\Parser\AST;


/**
 * Include statements that includes a single TypoScript file.
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST
 */
class FileIncludeStatement extends IncludeStatement
{



    /**
     * The name of the file to include.
     * @var string
     */
    public $filename;



    /**
     * Constructs a new include statement.
     *
     * @param string $filename   The name of the file to include.
     * @param int    $sourceLine The original source line.
     */
    public function __construct($filename, $sourceLine)
    {
        parent::__construct($sourceLine);
        $this->filename = $filename;
    }
} 