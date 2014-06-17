<?php
namespace Helmich\TsParser\Parser\Traverser;


use Helmich\TsParser\Parser\AST\ConditionalStatement;
use Helmich\TsParser\Parser\AST\NestedAssignment;
use Helmich\TsParser\Parser\AST\Statement;

class Traverser
{



    /**
     * @var \Helmich\TsParser\Parser\AST\Statement[]
     */
    private $statements;


    /** @var \Helmich\TsParser\Parser\Traverser\AggregatingVisitor */
    private $visitors;



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[] $statements
     */
    public function __construct(array $statements)
    {
        $this->statements = $statements;
        $this->visitors   = new AggregatingVisitor();
    }



    /**
     * @param \Helmich\TsParser\Parser\Traverser\Visitor $visitor
     */
    public function addVisitor(Visitor $visitor)
    {
        $this->visitors->addVisitor($visitor);
    }



    public function walk()
    {
        $this->visitors->enterTree();
        $this->walkRecursive($this->statements);
        $this->visitors->exitTree();
    }



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[] $statements
     * @return \Helmich\TsParser\Parser\AST\Statement[]
     */
    private function walkRecursive(array $statements)
    {
        foreach ($statements as $key => $statement)
        {
            $this->visitors->enterNode($statement);

            if ($statement instanceof NestedAssignment)
            {
                $statement->statements = $this->walkRecursive($statement->statements);
            }
            else if ($statement instanceof ConditionalStatement)
            {
                $statement->ifStatements   = $this->walkRecursive($statement->ifStatements);
                $statement->elseStatements = $this->walkRecursive($statement->elseStatements);
            }

            $this->visitors->exitNode($statement);
        }
        return $statements;
    }

}