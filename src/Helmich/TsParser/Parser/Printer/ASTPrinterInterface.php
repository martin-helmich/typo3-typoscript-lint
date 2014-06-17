<?php
namespace Helmich\TsParser\Parser\Printer;


use Symfony\Component\Console\Output\OutputInterface;

interface ASTPrinterInterface
{



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[]          $statements
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    public function printStatements(array $statements, OutputInterface $output);

}