<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Functional\Linter;

use Helmich\TypoScriptLint\Linter\Linter;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Sniff\SniffLocator;
use Helmich\TypoScriptLint\Logging\NullLogger;
use Helmich\TypoScriptParser\Parser\Parser;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class LinterTest extends TestCase
{

    /** @var  Linter */
    private $linter;

    public function setUp(): void
    {
        $tokenizer = new Tokenizer();
        $parser = new Parser($tokenizer);
        $locator = new SniffLocator();

        $this->linter = new Linter(
            $tokenizer,
            $parser,
            $locator
        );
    }

    /**
     * @dataProvider getFunctionalTestFixtures
     *
     * @param string $typoscriptFile
     * @param array $expectedWarnings
     */
    public function testLinterCreatesExpectedOutput(string $typoscriptFile, array $expectedWarnings)
    {
        $localConfigFilename = dirname($typoscriptFile) . '/tslint.yml';
        $localConfigData = [];
        if (file_exists($localConfigFilename)) {
            $localConfigData = Yaml::parse(file_get_contents($localConfigFilename));
        }

        $globalConfigData = Yaml::parse(file_get_contents(__DIR__ . '/Fixtures/typoscript-lint.dist.yml'));

        $report = new Report();
        $config = new LinterConfiguration();

        $processor = new Processor();
        $processed = $processor->processConfiguration($config, [$globalConfigData, $localConfigData]);

        $config->setConfiguration($processed);

        $this->linter->lintFile(
            $typoscriptFile,
            $report,
            $config,
            new NullLogger()
        );

        $printActualWarnings = function () use ($report) {
            $actualWarnings = $report->getFiles()[0]->getIssues();
            $content = "";
            foreach ($actualWarnings as $warning) {
                $content .= $warning->getLine() . ";" . $warning->getColumn() . ";" . $warning->getMessage() . ";" .
                    $warning->getSeverity() . ";" . $warning->getSource() . "\n";
            }
            return $content;
        };

        if (getenv("UPDATE_SNAPSHOTS")) {
            $output = dirname($typoscriptFile) . '/output.txt';
            file_put_contents($output, trim($printActualWarnings()));

            $this->markTestIncomplete();
            return;
        }

        if (count($expectedWarnings) === 0 && count($report->getFiles()) > 0) {
            $this->fail($printActualWarnings());
        }

        $this->assertCount(count($expectedWarnings) > 0 ? 1 : 0, $report->getFiles());
        if (count($expectedWarnings) > 0) {
            $actualWarnings = $report->getFiles()[0]->getIssues();
            $this->assertEquals($expectedWarnings, $actualWarnings, $printActualWarnings());
        }
    }

    public function getFunctionalTestFixtures()
    {
        $files = glob(__DIR__ . '/Fixtures/*/*.typoscript');
        $testCases = [];

        foreach ($files as $file) {
            $output = dirname($file) . '/output.txt';
            $outputLines = explode("\n", file_get_contents($output));
            $outputLines = array_filter($outputLines, 'strlen');

            $reports = array_map(
                function ($line) {
                    $values = str_getcsv($line, ';');
                    return new Issue(
                        (int)$values[0],
                        (int)$values[1],
                        $values[2],
                        $values[3],
                        $values[4]
                    );
                },
                $outputLines
            );

            $testCaseName = basename(dirname($file));
            $testCases[$testCaseName] = [$file, $reports];
        }

        return $testCases;
    }
}
