<?php
namespace Helmich\TsParser\Parser\AST;


class ConditionalStatement
{



    /** @var string */
    public $condition;


    /** @var \Helmich\TsParser\Parser\AST\Statement[] */
    public $ifStatements = [];


    /** @var \Helmich\TsParser\Parser\AST\Statement[] */
    public $elseStatements = [];



    public function __construct($condition, array $ifStatements, array $elseStatements = [])
    {
        $this->condition      = $condition;
        $this->ifStatements   = $ifStatements;
        $this->elseStatements = $elseStatements;
    }



}