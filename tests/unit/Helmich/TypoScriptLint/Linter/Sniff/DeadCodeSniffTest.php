<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff;
use Helmich\TypoScriptParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff
 * @uses   \Helmich\TypoScriptLint\Linter\Report\File
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Issue
 */
class DeadCodeSniffTest extends TestCase
{

    /** @var  DeadCodeSniff */
    private $sniff;

    public function setUp(): void
    {
        $this->sniff = new DeadCodeSniff([]);
    }

    public function testWarningIsGeneratedForCommentsThatLookLikeCode()
    {
        $tokens = [
            new Token(Token::TYPE_OBJECT_IDENTIFIER, 'foo', 1),
            new Token(Token::TYPE_OPERATOR_ASSIGNMENT, '=', 1),
            new Token(Token::TYPE_RIGHTVALUE, 'test', 1),
            new Token(Token::TYPE_COMMENT_ONELINE, 'foo = test2', 2)
        ];

        $file = new File('file');

        $this->sniff->sniff($tokens, $file, new LinterConfiguration());

        $warnings = $file->getIssues();

        assertCount(1, $warnings);
        assertEquals('Found commented code (foo = test2).', $warnings[0]->getMessage());
        assertEquals(2, $warnings[0]->getLine());
    }
}
