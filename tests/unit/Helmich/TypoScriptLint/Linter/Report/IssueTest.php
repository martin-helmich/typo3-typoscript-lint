<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Report\Issue
 * @uses   \Helmich\TypoScriptParser\Parser\ParseError
 */
class IssueTest extends TestCase
{

    private Issue $issue;

    public function setUp(): void
    {
        $this->issue = new Issue(200, 23, 'Issue message', Issue::SEVERITY_WARNING, self::class);
    }

    public function testConstructorSetsLine(): void
    {
        assertEquals(200, $this->issue->getLine());
    }

    public function testConstructorSetsColumn(): void
    {
        assertEquals(23, $this->issue->getColumn());
    }

    public function testConstructorSetsMessage(): void
    {
        assertEquals('Issue message', $this->issue->getMessage());
    }

    public function testConstructorSetsSeverity(): void
    {
        assertEquals(Issue::SEVERITY_WARNING, $this->issue->getSeverity());
    }

    public function testConstructorSetsSource(): void
    {
        assertEquals(self::class, $this->issue->getSource());
    }

    public function testWarningCanBeCreatedFromParseError(): void
    {
        $parseError = new ParseError('All is wrong!', 0, 1234);

        $issue = Issue::createFromParseError($parseError);

        assertEquals('Parse error: All is wrong!', $issue->getMessage());
        assertEquals(Issue::SEVERITY_ERROR, $issue->getSeverity());
        assertEquals(1234, $issue->getLine());
    }

    public function testWarningCanBeCreatedFromTokenizerError(): void
    {
        $tokenizerError = new TokenizerException('Could not read stuff', 0, null, 4321);

        $issue = Issue::createFromTokenizerError($tokenizerError);

        assertEquals('Tokenization error: Could not read stuff', $issue->getMessage());
        assertEquals(Issue::SEVERITY_ERROR, $issue->getSeverity());
        assertEquals(4321, $issue->getLine());
    }
}
