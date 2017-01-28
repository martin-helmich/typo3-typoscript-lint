<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\DuplicateAssignmentVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\NestingConsistencyVisitor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Scalar;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Sniff\Visitor\NestingConsistencyVisitor
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Issue
 *
 * @medium
 */
class NestingConsistencyVisitorTest extends \PHPUnit_Framework_TestCase
{

    /** @var DuplicateAssignmentVisitor */
    private $visitor;

    public function setUp()
    {
        $this->visitor = new NestingConsistencyVisitor();
    }

    public function testWarningIsGeneratedForDuplicateNestingStatements()
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

        $this->assertCount(1, $warnings);
        $this->assertEquals(
            'Multiple nested statements for object path "foo". Consider merging them into one statement.',
            $warnings[0]->getMessage()
        );
    }

    public function testWarningIsGeneratedForMultipleAssignmentsWithCommonPrefix()
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('test1'), 1),
            new Assignment(new ObjectPath('foo.baz', 'foo.baz'), new Scalar('test2'), 2),
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getIssues();

        $this->assertCount(2, $warnings);
        $this->assertEquals(
            'Common path prefix with assignment to "foo.baz" in line 2. Consider merging them into a nested assignment.',
            $warnings[0]->getMessage()
        );
        $this->assertEquals(
            'Common path prefix with assignment to "foo.bar" in line 1. Consider merging them into a nested assignment.',
            $warnings[1]->getMessage()
        );
    }

    public function testWarningIsGeneratedForAssignmentWhenNestedAssignmentWithCommonPrefixExists()
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

        $this->assertCount(1, $warnings);
        $this->assertEquals(
            'Assignment to value "foo.baz", altough nested statement for path "foo" exists at line 1.',
            $warnings[0]->getMessage()
        );
    }

    public function testConditionalStatementsDoNotRaiseWarnings()
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('test'), 1),
            new ConditionalStatement(
                '[globalString = ENV:test = foo]',
                [new Assignment(new ObjectPath('foo.baz', 'foo.baz'), new Scalar('blub'), 3)],
                [],
                3
            )
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getIssues();

        $this->assertCount(0, $warnings);
    }

    private function applyVisitorOnStatements(array $statements)
    {
        $traverser = new Traverser($statements);
        $traverser->addVisitor($this->visitor);
        $traverser->walk();
    }
}
