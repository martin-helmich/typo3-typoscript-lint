<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\DuplicateAssignmentVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\NestingConsistencyVisitor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Scalar;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\countOf;

#[CoversClass(NestingConsistencyVisitor::class)]
class NestingConsistencyVisitorTest extends TestCase
{

    private NestingConsistencyVisitor $visitor;

    public function setUp(): void
    {
        $this->visitor = new NestingConsistencyVisitor();
    }

    public function testWarningIsGeneratedForDuplicateNestingStatements(): void
    {
        $statements = [
            new NestedAssignment(
                new ObjectPath('foo', 'foo'),
                [new Assignment(new ObjectPath('foo.bar', 'bar'), new Scalar('test'), 2)],
                1
            ),
            new NestedAssignment(
                new ObjectPath('foo', 'foo'),
                [new Assignment(new ObjectPath('foo.baz', 'baz'), new Scalar('test2'), 4)],
                3
            ),
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getIssues();

        assertThat($warnings, countOf(1));
        assertEquals(
            'Multiple nested statements for object path "foo". Consider merging them into one statement.',
            $warnings[0]->getMessage()
        );
    }

    public function testWarningIsGeneratedForMultipleAssignmentsWithCommonPrefix(): void
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('test1'), 1),
            new Assignment(new ObjectPath('foo.baz', 'foo.baz'), new Scalar('test2'), 2),
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getIssues();

        assertThat($warnings, countOf(2));
        assertEquals(
            'Common path prefix "foo" with operation to "foo.baz" in line 2. Consider merging them into a nested statement.',
            $warnings[0]->getMessage()
        );
        assertEquals(
            'Common path prefix "foo" with operation to "foo.bar" in line 1. Consider merging them into a nested statement.',
            $warnings[1]->getMessage()
        );
    }

    public function testThresholdForCommonPrefixWarningIsConfigurable(): void
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('test1'), 1),
            new Assignment(new ObjectPath('foo.baz', 'foo.baz'), new Scalar('test2'), 2),
        ];

        $visitor = new NestingConsistencyVisitor(2);
        $traverser = new Traverser($statements);
        $traverser->addVisitor($visitor);
        $traverser->walk();

        $warnings = $this->visitor->getIssues();

        assertThat($warnings, countOf(0));
    }

    public function testWarningIsGeneratedForAssignmentWhenNestedAssignmentWithCommonPrefixExists(): void
    {
        $statements = [
            new NestedAssignment(
                new ObjectPath('foo', 'foo'),
                [new Assignment(new ObjectPath('foo.bar', 'bar'), new Scalar('test2'), 2)],
                1
            ),
            new Assignment(new ObjectPath('foo.baz', 'foo.baz'), new Scalar('test2'), 3),
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getIssues();

        assertThat($warnings, countOf(1));
        assertEquals(
            'Operation on value "foo.baz", altough nested statement for path "foo" exists at line 1.',
            $warnings[0]->getMessage()
        );
    }

    public function testConditionalStatementsDoNotRaiseWarnings(): void
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('test'), 1),
            new ConditionalStatement(
                '[globalString = ENV:test = foo]',
                [new Assignment(new ObjectPath('foo.baz', 'foo.baz'), new Scalar('blub'), 3)],
                [],
                3
            ),
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getIssues();

        assertThat($warnings, countOf(0));
    }

    private function applyVisitorOnStatements(array $statements): void
    {
        $traverser = new Traverser($statements);
        $traverser->addVisitor($this->visitor);
        $traverser->walk();
    }
}
