<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class AutofixReportPrinter implements Printer
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
        foreach ($report->getFiles() as $file) {
            $relativeFilename = PathUtils::getRelativePath($file->getFilename());

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
                $diff = $differ->diff($file->getOriginalContent(), $fixedContent);
                $this->output->write($diff);
            }
        }
    }
}
