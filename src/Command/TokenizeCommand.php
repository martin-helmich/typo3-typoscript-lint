<?php declare(strict_types=1);
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

    /** @var TokenizerInterface */
    private $tokenizer;

    /** @var TokenPrinterInterface */
    private $tokenPrinter;

    /**
     * Injects a tokenizer.
     *
     * @internal
     * @param TokenizerInterface $tokenizer The tokenizer.
     * @return void
     */
    public function injectTokenizer(TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }

    /**
     * Injects a token printer.
     *
     * @internal
     * @param TokenPrinterInterface $tokenPrinter The token printer.
     * @return void
     */
    public function injectTokenPrinter(TokenPrinterInterface $tokenPrinter): void
    {
        $this->tokenPrinter = $tokenPrinter;
    }

    /**
     * Configures this command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('tokenize')
            ->setDescription('Produce a token stream from a TypoScript input file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }

    /**
     * Executes this command.
     *
     * @param InputInterface  $input  Input options.
     * @param OutputInterface $output Output stream.
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $filename = $input->getArgument('filename');

        $output->writeln("Parsing input file <comment>{$filename}</comment>.");

        $tokens = $this->tokenizer->tokenizeStream($filename);

        $output->write($this->tokenPrinter->printTokenStream($tokens));
    }
}
