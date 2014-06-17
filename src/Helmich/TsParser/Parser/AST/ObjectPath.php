<?php
namespace Helmich\TsParser\Parser\AST;


/**
 * An object path.
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST
 */
class ObjectPath
{



    /**
     * The relative object path, as specified in the source code.
     * @var string
     */
    public $relativeName;


    /**
     * The absolute object path, as evaluated from parent nested statements.
     * @var
     */
    public $absoluteName;



    /**
     * Constructs a new object path.
     *
     * @param string $absoluteName The absolute object path.
     * @param string $relativeName The relative object path.
     */
    public function __construct($absoluteName, $relativeName)
    {
        $this->absoluteName = $absoluteName;
        $this->relativeName = $relativeName;
    }



}