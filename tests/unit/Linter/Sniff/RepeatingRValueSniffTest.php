<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Sniff\RepeatingRValueSniff;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

class RepeatingRValueSniffTest extends TestCase
{
    public function testRepeatedRValuesAboveThresholdAreReportedAsIssue()
    {
        $code = <<<EOF
foo = foobarbaz
baz = foobarbaz
EOF;
        $tokens = (new Tokenizer())->tokenizeString($code);
        $sniff = new RepeatingRValueSniff([]);
        $file = new File("file", $code);
        $sniff->sniff($tokens, $file, new LinterConfiguration());

        assertThat($file->getIssues(), countOf(1));
    }

    /**
     * @depends testRepeatedRValuesAboveThresholdAreReportedAsIssue
     */
    public function testRepeatingRValuesCanBeAllowedByWhitelist()
    {
        $code = <<<EOF
foo = foobarbaz
baz = foobarbaz
EOF;
        $tokens = (new Tokenizer())->tokenizeString($code);
        $sniff = new RepeatingRValueSniff(["allowedRightValues" => ["foobarbaz"]]);
        $file = new File("file", $code);
        $sniff->sniff($tokens, $file, new LinterConfiguration());

        assertThat($file->getIssues(), countOf(0));
    }
}
