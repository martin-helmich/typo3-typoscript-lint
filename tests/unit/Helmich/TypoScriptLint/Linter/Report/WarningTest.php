<?php
namespace Helmich\TypoScriptLint\Linter\Report;
use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;


/**
 * @covers Helmich\TypoScriptLint\Linter\Report\Warning
 * @uses Helmich\TypoScriptParser\Parser\ParseError
 */
class WarningTest extends \PHPUnit_Framework_TestCase
{



    /** @var Warning */
    private $warning;



    public function setUp()
    {
        $this->warning = new Warning(200, 23, 'Warning message', Warning::SEVERITY_WARNING, __CLASS__);
    }



    public function testConstructorSetsLine()
    {
        $this->assertEquals(200, $this->warning->getLine());
    }



    public function testConstructorSetsColumn()
    {
        $this->assertEquals(23, $this->warning->getColumn());
    }



    public function testConstructorSetsMessage()
    {
        $this->assertEquals('Warning message', $this->warning->getMessage());
    }



    public function testConstructorSetsSeverity()
    {
        $this->assertEquals(Warning::SEVERITY_WARNING, $this->warning->getSeverity());
    }



    public function testConstructorSetsSource()
    {
        $this->assertEquals(__CLASS__, $this->warning->getSource());
    }



    public function testWarningCanBeCreatedFromParseError()
    {
        $parseError = new ParseError('All is wrong!', 0, 1234);

        $warning = Warning::createFromParseError($parseError);

        $this->assertEquals('Parse error: All is wrong!', $warning->getMessage());
        $this->assertEquals(Warning::SEVERITY_ERROR, $warning->getSeverity());
        $this->assertEquals(1234, $warning->getLine());
    }



    public function testWarningCanBeCreatedFromTokenizerError()
    {
        $tokenizerError = new TokenizerException('Could not read stuff', 0, NULL, 4321);

        $warning = Warning::createFromTokenizerError($tokenizerError);

        $this->assertEquals('Tokenization error: Could not read stuff', $warning->getMessage());
        $this->assertEquals(Warning::SEVERITY_ERROR, $warning->getSeverity());
        $this->assertEquals(4321, $warning->getLine());
    }

}