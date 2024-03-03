<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Sniff\RepeatingRValueSniff;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\countOf;

#[CoversClass(RepeatingRValueSniff::class)]
class RepeatingRValueSniffTest extends TestCase
{
    public function testRepeatedRValuesAboveThresholdAreReportedAsIssue(): void
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

    #[Depends("testRepeatedRValuesAboveThresholdAreReportedAsIssue")]
    public function testRepeatingRValuesCanBeAllowedByWhitelist(): void
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
