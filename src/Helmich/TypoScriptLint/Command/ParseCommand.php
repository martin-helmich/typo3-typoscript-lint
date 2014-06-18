<?php
namespace Helmich\TypoScriptLint\Command;


use Helmich\TypoScriptParser\Parser\ParserInterface;
use Helmich\TypoScriptParser\Parser\Printer\PrettyPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ParseCommand extends Command
{



    /**
     * @var \Helmich\TypoScriptParser\Parser\ParserInterface
     */
    private $parser;



    public function injectParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }



    protected function configure()
    {
        $this
            ->setName('parse')
            ->setDescription('Parse TypoScript file into syntax tree.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $printer = new PrettyPrinter();
        $statements = $this->parser->parseStream($filename);

        $printer->printStatements($statements, $output);
    }


}