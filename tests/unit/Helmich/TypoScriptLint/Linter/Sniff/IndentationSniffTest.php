<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Sniff\IndentationSniff;
use Helmich\TypoScriptParser\Tokenizer\Token;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\countOf;
use function PHPUnit\Framework\equalTo;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Sniff\IndentationSniff
 * @uses   \Helmich\TypoScriptLint\Linter\Report\File
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Issue
 */
class IndentationSniffTest extends TestCase
{

    /** @var  IndentationSniff */
    private $sniff;

    public function setUp(): void
    {
        $this->sniff = new IndentationSniff([]);
    }

    public function testNoWarningIsGeneratedForNestedConditions()
    {
        $tokens = [
            new Token(Token::TYPE_CONDITION, '[page|uid = 0] ', 0),
            new Token(Token::TYPE_OBJECT_IDENTIFIER, 'foo', 1),
            new Token(Token::TYPE_OPERATOR_ASSIGNMENT, '=', 2),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 2),
            new Token(Token::TYPE_COMMENT_ONELINE, 'foo = test2', 3),
            new Token(Token::TYPE_CONDITION, '[page|uid = 1] ', 4),
            new Token(Token::TYPE_OBJECT_IDENTIFIER, 'foo', 5),
            new Token(Token::TYPE_OPERATOR_ASSIGNMENT, '=', 6),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 7),
            new Token(Token::TYPE_CONDITION_END, '[GLOBAL] ', 8),
        ];

        $file = new File('file');

        $this->sniff->sniff($tokens, $file, new LinterConfiguration());

        $warnings = $file->getIssues();

        $this->assertCount(0, $warnings);
    }

    /**
     * @see https://github.com/martin-helmich/typo3-typoscript-lint/issues/79
     */
    public function testNoNegativeIndentationLevelsForSuperflousClosingToken()
    {
        $sniff = new IndentationSniff(["indentConditions" => true]);
        $tokens = (new Tokenizer())->tokenizeString(<<<EOF
foo {
    bar = 1
}
[global]
baz = 1
EOF
        );
        $file = new File("file");

        $sniff->sniff($tokens, $file, new LinterConfiguration());

        $warnings = $file->getIssues();
        assertThat($warnings, countOf(0));
    }

    /**
     * @see https://github.com/martin-helmich/typo3-typoscript-lint/issues/88
     */
    public function testNoNegativeIndentationLevels()
    {
        $code = <<<EOF
plugin.tx_solr {
    # ...
}
[extensionLoaded("persons")]
plugin.tx_solr {
    #...
}
[end]
EOF;

        $sniff = new IndentationSniff([]);
        $tokens = (new Tokenizer())->tokenizeString($code);
        $file = new File("file");

        $sniff->sniff($tokens, $file, new LinterConfiguration());

        $warnings = $file->getIssues();
        assertThat($warnings, equalTo([]));
    }

    /**
     * @see https://github.com/martin-helmich/typo3-typoscript-lint/issues/101
     */
    public function testWarningIsGeneratedForNotIndentedLinesInConditions()
    {
        $code = <<<EOF
[globalString = GP:foo = 1]
foo.bar = 3
[global]
EOF;

        $sniff = new IndentationSniff(["indentConditions" => true]);
        $tokens = (new Tokenizer())->tokenizeString($code);
        $file = new File("file");

        $sniff->sniff($tokens, $file, new LinterConfiguration());

        $warnings = $file->getIssues();

        $this->assertCount(1, $warnings);
        $this->assertEquals('Expected indent of 4 spaces.', $warnings[0]->getMessage());
        $this->assertEquals(2, $warnings[0]->getLine());
    }
}
