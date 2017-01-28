<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\DuplicateAssignmentVisitor;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Scalar;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Sniff\Visitor\DuplicateAssignmentVisitor
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Warning
 *
 * @medium
 */
class DuplicateAssignmentVisitorTest extends \PHPUnit_Framework_TestCase
{

    /** @var DuplicateAssignmentVisitor */
    private $visitor;

    public function setUp()
    {
        $this->visitor = new DuplicateAssignmentVisitor();
    }

    public function testWarningIsGeneratedForDuplicateAssignmentOnSameHierarchy()
    {
        $statements = [
            new Assignment(new ObjectPath('foo', 'foo'), new Scalar('bar'), 1),
            new Assignment(new ObjectPath('foo', 'foo'), new Scalar('baz'), 2),
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getWarnings();

        $this->assertCount(1, $warnings);
        $this->assertEquals('Value of object "foo" is overwritten in line 2.', $warnings[0]->getMessage());
    }

    public function testWarningIsGeneratedForDuplicateAssignmentOnAcrossNestedAssignments()
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('bar'), 1),
            new NestedAssignment(
                new ObjectPath('foo', 'foo'), [
                new Assignment(new ObjectPath('foo.bar', 'bar'), new Scalar('baz'), 3)
            ], 2
            )
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getWarnings();

        $this->assertCount(1, $warnings);
        $this->assertEquals('Value of object "foo.bar" is overwritten in line 3.', $warnings[0]->getMessage());
    }

    public function testNoWarningIsGeneratedWhenValueIsOverwrittenInCondition()
    {
        $statements = [
            new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('bar'), 1),
            new ConditionalStatement(
                '[globalString = ENV:foo = bar]',
                [new Assignment(new ObjectPath('foo.bar', 'foo.bar'), new Scalar('baz'), 3)],
                [],
                2
            )
        ];

        $this->applyVisitorOnStatements($statements);

        $warnings = $this->visitor->getWarnings();

        $this->assertCount(0, $warnings);
    }

    private function applyVisitorOnStatements(array $statements)
    {
        $traverser = new Traverser($statements);
        $traverser->addVisitor($this->visitor);
        $traverser->walk();
    }
}
