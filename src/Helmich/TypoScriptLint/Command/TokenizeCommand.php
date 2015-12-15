<?php
namespace Helmich\TypoScriptLint\Command;

use Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface;
use Helmich\TypoScriptParser\Tokenizer\TokenizerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper command that generates a token stream from a TypoScript input file.
 *
 * Probably without any real application; this command is quite useful for
 * debugging the tokenizer, though.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Command
 */
class TokenizeCommand extends Command
{

    /** @var \Helmich\TypoScriptParser\Tokenizer\TokenizerInterface */
    private $tokenizer;

    /** @var \Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface */
    private $tokenPrinter;

    /**
     * Injects a tokenizer.
     *
     * @internal
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenizerInterface $tokenizer The tokenizer.
     * @return void
     */
    public function injectTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    /**
     * Injects a token printer.
     *
     * @internal
     * @param \Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface $tokenPrinter The token printer.
     * @return void
     */
    public function injectTokenPrinter(TokenPrinterInterface $tokenPrinter)
    {
        $this->tokenPrinter = $tokenPrinter;
    }

    /**
     * Configures this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('tokenize')
            ->setDescription('Produce a token stream from a TypoScript input file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }

    /**
     * Executes this command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Input options.
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output stream.
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $output->writeln("Parsing input file <comment>{$filename}</comment>.");

        $tokens = $this->tokenizer->tokenizeStream($filename);

        $output->write($this->tokenPrinter->printTokenStream($tokens));
    }
}