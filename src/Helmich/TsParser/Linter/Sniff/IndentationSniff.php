<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Report\Warning;
use Helmich\TsParser\Tokenizer\LineGrouper;
use Helmich\TsParser\Tokenizer\TokenInterface;

class IndentationSniff implements TokenStreamSniffInterface
{



    private $useSpaces = TRUE;


    private $indentPerLevel = 4;



    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if (array_key_exists('useSpaces', $parameters))
        {
            $this->useSpaces = $parameters['useSpaces'];
        }
        if (array_key_exists('indentPerLevel', $parameters))
        {
            $this->indentPerLevel = $parameters['indentPerLevel'];
        }
    }



    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TsParser\Linter\Report\File         $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration $configuration
     * @return mixed
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration)
    {
        $indentCharacter  = $this->useSpaces ? ' ' : "\t";
        $tokensByLine     = new LineGrouper($tokens);
        $indentationLevel = 0;

        /** @var \Helmich\TsParser\Tokenizer\TokenInterface[] $tokensInLine */
        foreach ($tokensByLine->getLines() as $line => $tokensInLine)
        {
            foreach ($tokensInLine as $key => $token)
            {
                if ($token->getType() === TokenInterface::TYPE_BRACE_CLOSE)
                {
                    $indentationLevel--;
                }

                if ($token->getType() === TokenInterface::TYPE_RIGHTVALUE_MULTILINE)
                {
                    unset($tokensInLine[$key]);
                    $tokensInLine = array_values($tokensInLine);
                }
            }
            $firstToken = count($tokensInLine) > 0 ? $tokensInLine[0] : NULL;

            // Skip empty lines.
            if (count($tokensInLine) == 1 && $firstToken->getType() === TokenInterface::TYPE_WHITESPACE && $firstToken->getValue() === "\n")
            {
                continue;
            }

            if ($indentationLevel === 0)
            {
                if ($tokensInLine[0]->getType() === TokenInterface::TYPE_WHITESPACE && strlen($tokensInLine[0]->getValue()))
                {
                    $file->addWarning($this->createWarning($line, $indentationLevel, $tokensInLine[0]->getValue()));
                }
            }
            else
            {
                if ($tokensInLine[0]->getType() !== TokenInterface::TYPE_WHITESPACE)
                {
                    $file->addWarning($this->createWarning($line, $indentationLevel, ''));
                }
                else
                {
                    $expectedIndentationCharacterCount = $this->indentPerLevel * $indentationLevel;
                    $expectedIndentation               = str_repeat($indentCharacter, $expectedIndentationCharacterCount);

                    if ($tokensInLine[0]->getValue() !== $expectedIndentation)
                    {
                        $file->addWarning($this->createWarning($line, $indentationLevel, $tokensInLine[0]->getValue()));
                    }
                }
            }

            foreach ($tokensInLine as $token)
            {
                if ($token->getType() === TokenInterface::TYPE_BRACE_OPEN)
                {
                    $indentationLevel++;
                }
            }
        }
    }



    private function createWarning($line, $expectedLevel, $actual)
    {
        $indentCharacterCount       = ($expectedLevel * $this->indentPerLevel);
        $indentCharacterDescription = ($this->useSpaces ? 'space' : 'tab') . (($indentCharacterCount == 1) ? '' : 's');

        $expectedMessage = "Expected indent of {$indentCharacterCount} {$indentCharacterDescription}.";

        return new Warning($line, strlen($actual), $expectedMessage, Warning::SEVERITY_WARNING, __CLASS__);
    }


}