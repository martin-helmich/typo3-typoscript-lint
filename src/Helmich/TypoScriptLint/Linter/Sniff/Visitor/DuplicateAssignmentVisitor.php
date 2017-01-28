<?php
namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

class DuplicateAssignmentVisitor implements SniffVisitor
{

    /** @var Assignment[] */
    private $assignments = [];

    /** @var Warning[] */
    private $warnings = [];

    private $inCondition = false;

    /**
     * @return Warning[]
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    public function enterTree(array $statements)
    {
    }

    public function enterNode(Statement $statement)
    {
        if ($statement instanceof ConditionalStatement) {
            $this->inCondition = true;
        }

        if ($statement instanceof Assignment && false === $this->inCondition) {
            if (isset($this->assignments[$statement->object->absoluteName])) {
                /** @var Statement $lastAssignment */
                $lastAssignment = $this->assignments[$statement->object->absoluteName];
                $this->warnings[] = new Warning(
                    $lastAssignment->sourceLine,
                    null,
                    sprintf(
                        'Value of object "%s" is overwritten in line %d.',
                        $statement->object->absoluteName,
                        $statement->sourceLine
                    ),
                    Warning::SEVERITY_WARNING,
                    'Helmich\TypoScriptLint\Linter\Sniff\DuplicateAssignmentSniff'
                );
            }

            $this->assignments[$statement->object->absoluteName] = $statement;
        }
    }

    public function exitNode(Statement $statement)
    {
        // Luckily, conditions cannot be nested. Phew.
        if ($statement instanceof ConditionalStatement) {
            $this->inCondition = false;
        }
    }

    public function exitTree(array $statements)
    {
    }
}
