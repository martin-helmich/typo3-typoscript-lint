<?php
namespace Helmich\TypoScriptLint\Tests\Linter;

use Helmich\TypoScriptLint\Linter\Linter;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff;
use Helmich\TypoScriptLint\Linter\Sniff\DuplicateAssignmentSniff;
use Helmich\TypoScriptLint\Linter\Sniff\IndentationSniff;
use Helmich\TypoScriptLint\Linter\Sniff\NestingConsistencySniff;
use Helmich\TypoScriptLint\Linter\Sniff\OperatorWhitespaceSniff;
use Helmich\TypoScriptLint\Linter\Sniff\RepeatingRValueSniff;
use Helmich\TypoScriptLint\Linter\Sniff\SniffLocator;
use Helmich\TypoScriptLint\Logging\NullLogger;
use Helmich\TypoScriptParser\Parser\Parser;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use Prophecy\Argument;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;

class LinterTest extends \PHPUnit_Framework_TestCase
{

    /** @var  Linter */
    private $linter;

    public function setUp()
    {
        $tokenizer = new Tokenizer();
        $parser    = new Parser($tokenizer);
        $locator   = new SniffLocator();

        $this->linter = new Linter(
            $tokenizer,
            $parser,
            $locator
        );
    }

    /**
     * @dataProvider getFunctionalTestFixtures
     */
    public function testLinterCreatesExpectedOutput($typoscriptFile, array $expectedWarnings)
    {
        $localConfigFilename = dirname($typoscriptFile) . '/tslint.yml';
        $localConfigData = [];
        if (file_exists($localConfigFilename)) {
            $localConfigData = Yaml::parse(file_get_contents($localConfigFilename));
        }

        $globalConfigData = Yaml::parse(file_get_contents(__DIR__ . '/Fixtures/tslint.dist.yml'));

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

        $printActualWarnings = function() use ($report) {
            $actualWarnings = $report->getFiles()[0]->getWarnings();
            $content = "";
            foreach ($actualWarnings as $warning) {
                $content .= $warning->getLine() . ";" . $warning->getColumn() . ";" . $warning->getMessage() . ";" .
                    $warning->getSeverity() . ";" . $warning->getSource() . "\n";
            }
            return $content;
        };

        if (count($expectedWarnings) === 0 && count($report->getFiles()) > 0) {
            $this->fail($printActualWarnings());
        }

        $this->assertCount(count($expectedWarnings) > 0 ? 1 : 0, $report->getFiles());
        if (count($expectedWarnings) > 0) {
            $actualWarnings = $report->getFiles()[0]->getWarnings();
            $this->assertEquals($expectedWarnings, $actualWarnings, $printActualWarnings());
        }
    }

    public function getFunctionalTestFixtures()
    {
        $files = glob(__DIR__ . '/Fixtures/*/*.typoscript');
        foreach ($files as $file) {
            $output      = dirname($file) . '/output.txt';
            $outputLines = explode("\n", file_get_contents($output));
            $outputLines = array_filter($outputLines, 'strlen');

            $reports = array_map(
                function ($line) use ($file) {
                    $values = str_getcsv($line, ';');
                    return new Warning(
                        $values[0],
                        $values[1],
                        $values[2],
                        $values[3],
                        $values[4]
                    );
                },
                $outputLines
            );

            yield [
                $file,
                $reports
            ];
        }
    }
}
