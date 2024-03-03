<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\OperatorWhitespaceSniff;
use Helmich\TypoScriptParser\Tokenizer\Token;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

class OperatorWhitespaceSniffTest extends TestCase
{
    private OperatorWhitespaceSniff $sniff;

    public function setUp(): void
    {
        $this->sniff = new OperatorWhitespaceSniff([]);
    }

    public static function getValidTokenSequences(): \Generator
    {
        yield [
            [
                new Token(TokenInterface::TYPE_OBJECT_IDENTIFIER, "foo", 1),
                new Token(TokenInterface::TYPE_OPERATOR_COPY, "<", 1),
                new Token(TokenInterface::TYPE_WHITESPACE, " ", 1),
                new Token(TokenInterface::TYPE_RIGHTVALUE, "bar", 1),
            ],
            [
                new Issue(1, 0, 'No whitespace after object accessor.', 'warning', OperatorWhitespaceSniff::class),
            ],
        ];
    }

    #[DataProvider('getValidTokenSequences')]
    public function testTokenSequenceGeneratesExpectedWarnings(array $tokens, array $warnings): void
    {
        $file = new File("sample.ts");
        $conf = new LinterConfiguration();

        $this->sniff->sniff($tokens, $file, $conf);

        assertThat($file->getIssues(), equalTo($warnings));
    }
}
