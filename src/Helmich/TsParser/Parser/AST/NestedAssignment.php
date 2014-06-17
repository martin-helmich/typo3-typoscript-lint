<?php
namespace Helmich\TsParser\Parser\AST;


class NestedAssignment extends Statement
{



    /** @var \Helmich\TsParser\Parser\AST\ObjectPath */
    public $object;


    /** @var \Helmich\TsParser\Parser\AST\Statement[] */
    public $statements;



    public function __construct(ObjectPath $object, array $statements)
    {
        $this->object     = $object;
        $this->statements = $statements;
    }
} 