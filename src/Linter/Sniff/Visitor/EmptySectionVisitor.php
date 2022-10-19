<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Sniff\EmptySectionSniff;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

class EmptySectionVisitor implements SniffVisitor
{
    /** @var Issue[] */
    private $issues = [];

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
        if (!($statement instanceof NestedAssignment)) {
            return;
        }

        if (count($statement->statements) === 0) {
            $this->issues[] = new Issue(
                $statement->sourceLine,
                null,
                "Empty assignment block",
                Issue::SEVERITY_WARNING,
                EmptySectionSniff::class
            );
        }
    }

    public function exitNode(Statement $statement): void
    {
    }

    public function exitTree(array $statements): void
    {
    }

}
