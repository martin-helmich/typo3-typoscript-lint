<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Report\Issue
 * @uses   \Helmich\TypoScriptParser\Parser\ParseError
 */
class IssueTest extends \PHPUnit_Framework_TestCase
{

    /** @var Issue */
    private $issue;

    public function setUp()
    {
        $this->issue = new Issue(200, 23, 'Issue message', Issue::SEVERITY_WARNING, __CLASS__);
    }

    public function testConstructorSetsLine()
    {
        $this->assertEquals(200, $this->issue->getLine());
    }

    public function testConstructorSetsColumn()
    {
        $this->assertEquals(23, $this->issue->getColumn());
    }

    public function testConstructorSetsMessage()
    {
        $this->assertEquals('Issue message', $this->issue->getMessage());
    }

    public function testConstructorSetsSeverity()
    {
        $this->assertEquals(Issue::SEVERITY_WARNING, $this->issue->getSeverity());
    }

    public function testConstructorSetsSource()
    {
        $this->assertEquals(__CLASS__, $this->issue->getSource());
    }

    public function testWarningCanBeCreatedFromParseError()
    {
        $parseError = new ParseError('All is wrong!', 0, 1234);

        $issue = Issue::createFromParseError($parseError);

        $this->assertEquals('Parse error: All is wrong!', $issue->getMessage());
        $this->assertEquals(Issue::SEVERITY_ERROR, $issue->getSeverity());
        $this->assertEquals(1234, $issue->getLine());
    }

    public function testWarningCanBeCreatedFromTokenizerError()
    {
        $tokenizerError = new TokenizerException('Could not read stuff', 0, null, 4321);

        $issue = Issue::createFromTokenizerError($tokenizerError);

        $this->assertEquals('Tokenization error: Could not read stuff', $issue->getMessage());
        $this->assertEquals(Issue::SEVERITY_ERROR, $issue->getSeverity());
        $this->assertEquals(4321, $issue->getLine());
    }
}
