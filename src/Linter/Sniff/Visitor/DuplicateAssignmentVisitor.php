<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\DuplicateAssignmentSniff;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

class DuplicateAssignmentVisitor implements SniffVisitor
{

    /** @var Assignment[] */
    private $assignments = [];

    /** @var Issue[] */
    private $issues = [];

    /** @var bool */
    private $inCondition = false;

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    public function enterTree(array $statements): void
    {
    }

    public function enterNode(Statement $statement): void
    {
        if ($statement instanceof ConditionalStatement) {
            $this->inCondition = true;
        }

        if ($statement instanceof Assignment && false === $this->inCondition) {
            if (isset($this->assignments[$statement->object->absoluteName])) {
                /** @var Statement $lastAssignment */
                $lastAssignment = $this->assignments[$statement->object->absoluteName];
                $this->issues[] = new Issue(
                    $lastAssignment->sourceLine,
                    null,
                    sprintf(
                        'Value of object "%s" is overwritten in line %d.',
                        $statement->object->absoluteName,
                        $statement->sourceLine
                    ),
                    Issue::SEVERITY_WARNING,
                    DuplicateAssignmentSniff::class
                );
            }

            $this->assignments[$statement->object->absoluteName] = $statement;
        }
    }

    public function exitNode(Statement $statement): void
    {
        // Luckily, conditions cannot be nested. Phew.
        if ($statement instanceof ConditionalStatement) {
            $this->inCondition = false;
        }
    }

    public function exitTree(array $statements): void
    {
    }
}
