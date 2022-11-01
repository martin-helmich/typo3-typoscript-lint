<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Sniff\RepeatingRValueSniff;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\countOf;

class RepeatingRValueSniffTest extends TestCase
{
    public function testRepeatedRValuesAboveThresholdAreReportedAsIssue()
    {
        $tokens = (new Tokenizer())->tokenizeString(<<<EOF
foo = foobarbaz
baz = foobarbaz
EOF
        );
        $sniff = new RepeatingRValueSniff([]);
        $file = new File("file");
        $sniff->sniff($tokens, $file, new LinterConfiguration());

        assertThat($file->getIssues(), countOf(1));
    }

    /**
     * @depends testRepeatedRValuesAboveThresholdAreReportedAsIssue
     */
    public function testRepeatingRValuesCanBeAllowedByWhitelist()
    {
        $tokens = (new Tokenizer())->tokenizeString(<<<EOF
foo = foobarbaz
baz = foobarbaz
EOF
        );
        $sniff = new RepeatingRValueSniff(["allowedRightValues" => ["foobarbaz"]]);
        $file = new File("file");
        $sniff->sniff($tokens, $file, new LinterConfiguration());

        assertThat($file->getIssues(), countOf(0));
    }
}
