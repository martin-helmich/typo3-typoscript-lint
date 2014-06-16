<?php
namespace Helmich\TsParser\Linter\ReportPrinter;


use Helmich\TsParser\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

class CheckstyleReportPrinter implements Printer
{



    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;



    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }



    public function writeReport(Report $report)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');

        $root = $xml->createElement('checkstyle');
        $root->setAttribute('version', 'tslint-1.0');

        foreach ($report->getFiles() as $file)
        {
            $xmlFile = $xml->createElement('file');
            $xmlFile->setAttribute('name', $file->getFilename());

            foreach ($file->getWarnings() as $warning)
            {
                $xmlWarning = $xml->createElement('error');
                $xmlWarning->setAttribute('line', $warning->getLine());
                $xmlWarning->setAttribute('severity', $warning->getSeverity());
                $xmlWarning->setAttribute('message', $warning->getMessage());
                $xmlWarning->setAttribute('source', $warning->getSource());

                if ($warning->getColumn() !== NULL)
                {
                    $xmlWarning->setAttribute('column', $warning->getColumn());
                }

                $xmlFile->appendChild($xmlWarning);
            }

            $root->appendChild($xmlFile);
        }

        $xml->appendChild($root);
        $xml->formatOutput = TRUE;

        $this->output->write($xml->saveXML());
    }
}