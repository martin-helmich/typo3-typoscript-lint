<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use DOMDocument;
use Helmich\TypoScriptLint\Application;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Report printer that generates CodeClimate JSON documents [1].
 *
 * These reports are well-suited for being used in continuous integration
 * environments like gitlab [2].
 *
 * [1] https://github.com/codeclimate/spec/blob/master/SPEC.md
 * [2] https://docs.gitlab.com/ee/user/project/merge_requests/code_quality.html
 *
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\ReportPrinter
 */
class CodeClimateReportPrinter implements Printer
{

    /** @var OutputInterface */
    private $output;

    /**
     * Constructs a new checkstyle report printer.
     *
     * @param OutputInterface $output Output stream to write on. Might be STDOUT or a file.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a report in checkstyle XML format.
     *
     * @param Report $report The report to print.
     * @return void
     */
    public function writeReport(Report $report): void
    {
        $issues = [];

        foreach ($report->getFiles() as $file) {
            foreach ($file->getIssues() as $issue) {
                $issueData = [
                    'type' => 'issue',
                    'check_name' => $issue->getSource(),
                    'description' => $issue->getMessage(),
                    'categories' => ['Style'],
                    'location' => [
                        'path' => $file->getFilename(),
                        'lines' =>  [
                            'begin' => $issue->getLine() ? ((string) $issue->getLine()) : 0
                        ]
                    ]
                ];

                $column = $issue->getColumn();
                if ($column !== null) {
                    $issueData['location']['lines']['column'] = $column;
                }

                $issueData['fingerprint'] = $this->fingerprint($issueData);

                $issues[] = $issueData;
            }
        }

        $this->output->write(json_encode($issues));
    }

    protected function fingerprint(array $issue)
    {
        return md5(json_encode($issue));
    }
}
