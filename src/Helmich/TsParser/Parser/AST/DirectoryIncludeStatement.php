<?php
namespace Helmich\TsParser\Parser\AST;


/**
 * Include statements that includes many TypoScript files from a directory.
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST
 */
class DirectoryIncludeStatement extends IncludeStatement
{



    /**
     * The directory to include from.
     * @var string
     */
    public $directory;


    /**
     * An optional file extension filter. May be NULL.
     * @var string
     */
    public $extension = NULL;



    /**
     * Constructs a new directory include statement.
     *
     * @param string $directory  The directory to include from.
     * @param string $extension  The file extension filter. MAY be NULL.
     * @param int    $sourceLine The original source line.
     */
    public function __construct($directory, $extension, $sourceLine)
    {
        parent::__construct($sourceLine);

        $this->directory = $directory;
        $this->extension = $extension;
    }


} 