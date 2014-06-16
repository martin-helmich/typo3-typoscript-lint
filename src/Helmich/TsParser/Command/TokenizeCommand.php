<?php
namespace Helmich\TsParser\Command;

use Helmich\TsParser\Tokenizer\TokenizerInterface;
use Helmich\TsParser\Tokenizer\Printer\TokenPrinterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TokenizeCommand extends Command
{



    /**
     * @var \Helmich\TsParser\Tokenizer\TokenizerInterface
     */
    private $tokenizer;


    /**
     * @var \Helmich\TsParser\Tokenizer\Printer\TokenPrinterInterface
     */
    private $tokenPrinter;



    public function injectTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }



    public function injectTokenPrinter(TokenPrinterInterface $tokenPrinter)
    {
        $this->tokenPrinter = $tokenPrinter;
    }



    protected function configure()
    {
        $this
            ->setName('tokenize')
            ->setDescription('Produce a token stream from a TypoScript input file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $output->writeln("Parsing input file <comment>{$filename}</comment>.");

        $tokens = $this->tokenizer->tokenizeStream($filename);

        $output->write($this->tokenPrinter->printTokenStream($tokens));
    }



}