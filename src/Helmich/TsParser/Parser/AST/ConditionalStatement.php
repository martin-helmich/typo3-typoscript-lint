<?php
namespace Helmich\TsParser\Parser\AST;


/**
 * A conditional statement with a condition, an if-branch and an optional else-branch.
 *
 * @package    Helmich\TsParser
 * @subpackage Parser\AST
 */
class ConditionalStatement extends Statement
{



    /**
     * The condition to evaluate.
     * @var string
     */
    public $condition;


    /**
     * Statements within the if-branch.
     * @var \Helmich\TsParser\Parser\AST\Statement[]
     */
    public $ifStatements = [];


    /**
     * Statements within the else-branch.
     * @var \Helmich\TsParser\Parser\AST\Statement[]
     */
    public $elseStatements = [];



    /**
     * Constructs a conditional statement.
     *
     * @param string                                   $condition      The condition statement
     * @param \Helmich\TsParser\Parser\AST\Statement[] $ifStatements   The statements in the if-branch.
     * @param \Helmich\TsParser\Parser\AST\Statement[] $elseStatements The statements in the else-branch (may be empty).
     * @param int                                      $sourceLine     The original source line.
     */
    public function __construct($condition, array $ifStatements, array $elseStatements, $sourceLine)
    {
        parent::__construct($sourceLine);

        $this->condition      = $condition;
        $this->ifStatements   = $ifStatements;
        $this->elseStatements = $elseStatements;
    }



}