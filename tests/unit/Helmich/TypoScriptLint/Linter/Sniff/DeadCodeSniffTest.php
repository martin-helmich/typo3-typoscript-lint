<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptParser\Tokenizer\Token;


/**
 * @covers Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff
 * @uses   Helmich\TypoScriptLint\Linter\Report\File
 * @uses   Helmich\TypoScriptLint\Linter\Report\Warning
 */
class DeadCodeSniffTest extends \PHPUnit_Framework_TestCase
{



    /** @var  DeadCodeSniff */
    private $sniff;



    public function setUp()
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

        $warnings = $file->getWarnings();

        $this->assertCount(1, $warnings);
        $this->assertEquals('Found commented code (foo = test2).', $warnings[0]->getMessage());
        $this->assertEquals(2, $warnings[0]->getLine());
    }

}