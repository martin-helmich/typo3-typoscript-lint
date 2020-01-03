<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\OperatorWhitespaceSniff;
use Helmich\TypoScriptParser\Tokenizer\Printer\CodeTokenPrinter;
use Helmich\TypoScriptParser\Tokenizer\Token;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use PHPUnit\Framework\TestCase;

class OperatorWhitespaceSniffTest extends TestCase
{
    /** @var OperatorWhitespaceSniff */
    private $sniff;

    public function setUp(): void
    {
        $this->sniff = new OperatorWhitespaceSniff([]);
    }

    public function getValidTokenSequences()
    {
        yield [
            [
                new Token(TokenInterface::TYPE_OBJECT_IDENTIFIER, "foo", 1),
                new Token(TokenInterface::TYPE_OPERATOR_COPY, "<", 1),
                new Token(TokenInterface::TYPE_WHITESPACE, " ", 1),
                new Token(TokenInterface::TYPE_RIGHTVALUE, "bar", 1)
            ],
            [
                new Issue(1, 4, 'No whitespace after object accessor.', 'warning', OperatorWhitespaceSniff::class, true)
            ]
        ];
    }

    /**
     * @param array $tokens
     * @param array $warnings
     * @return void
     * @dataProvider getValidTokenSequences
     */
    public function testTokenSequenceGeneratesExpectedWarnings(array $tokens, array $warnings)
    {
        $file = new File("sample.ts", (new CodeTokenPrinter())->printTokenStream($tokens));
        $conf = new LinterConfiguration();

        $this->sniff->sniff($tokens, $file, $conf);

        assertThat($file->getIssues(), equalTo($warnings));
    }
}
