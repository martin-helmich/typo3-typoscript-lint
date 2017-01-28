<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\OperatorWhitespaceSniff;
use Helmich\TypoScriptParser\Tokenizer\Token;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class OperatorWhitespaceSniffTest extends \PHPUnit_Framework_TestCase
{
    /** @var OperatorWhitespaceSniff */
    private $sniff;

    public function setUp()
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
                new Issue(1, null, 'No whitespace after object accessor.', 'warning', OperatorWhitespaceSniff::class)
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
        $file = new File("sample.ts");
        $conf = new LinterConfiguration();

        $this->sniff->sniff($tokens, $file, $conf);

        assertThat($file->getIssues(), equalTo($warnings));
    }
}