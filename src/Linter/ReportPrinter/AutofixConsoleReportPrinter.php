<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class AutofixConsoleReportPrinter implements Printer
{

    /** @var OutputInterface */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeReport(Report $report): void
    {
        $this->output->writeln('');
        $this->output->writeln('<comment>AUTOFIX PREVIEW</comment>');

        $formatter = $this->output->getFormatter();
        $formatter->setStyle("diff-add", new OutputFormatterStyle("green"));
        $formatter->setStyle("diff-rm", new OutputFormatterStyle("red"));


        foreach ($report->getFiles() as $file) {
            $relativeFilename = $file->getFilename();
            if (substr($relativeFilename, 0, mb_strlen(getcwd())) === getcwd()) {
                $relativeFilename = substr($relativeFilename, mb_strlen(getcwd()));
                $relativeFilename = ltrim($relativeFilename, DIRECTORY_SEPARATOR);
            }

            $builder = new StrictUnifiedDiffOutputBuilder([
                'collapseRanges'      => true,
                'commonLineThreshold' => 6,
                'contextLines'        => 3,
                'fromFile'            => "a/{$relativeFilename}",
                'fromFileDate'        => null,
                'toFile'              => "b/{$relativeFilename}",
                'toFileDate'          => null,
            ]);
            $differ = new Differ($builder);

            $fixedContent = $file->getFixedContent();
            if ($fixedContent !== null && $file->getOriginalContent() !== $fixedContent) {
                $this->output->writeln("");
                $this->output->writeln("=> <comment>{$relativeFilename}</comment>");
                $this->output->writeln("");

                $diff = $differ->diff($file->getOriginalContent(), $fixedContent);
                $lines = explode("\n", $diff);

                foreach ($lines as $line) {
                    $hasPrefix = function(string $prefix) use ($line): bool {
                        return mb_substr($line, 0, mb_strlen($prefix)) === $prefix;
                    };

                    if (($hasPrefix(" ")) ||
                        ($hasPrefix("+") && !$hasPrefix("+++")) ||
                        ($hasPrefix("-") && !$hasPrefix("---"))) {
                        $line = str_replace(" ", "·", $line);
                        $line = str_replace("\t", "⇥", $line);
                    }

                    if (mb_strlen($line) > 0 && mb_substr($line, 0, 1) === "·") {
                        $line = " " . mb_substr($line, 1);
                    }

                    if ($hasPrefix("+")) {
                        $this->output->writeln("    <diff-add>$line</diff-add>");
                    } else if ($hasPrefix("-")) {
                        $this->output->writeln("    <diff-rm>$line</diff-rm>");
                    } else {
                        $this->output->writeln("    " . $line);
                    }
                }
            }
        }

        $this->output->writeln('To apply these patches, pipe this command\'s output into <comment>patch -u -p1</comment>:');
        $this->output->writeln("");
        $this->output->writeln(sprintf('    <comment>%s | patch -u -p1</comment>', join(" ", $GLOBALS["argv"])));
        $this->output->writeln("");
    }
}
