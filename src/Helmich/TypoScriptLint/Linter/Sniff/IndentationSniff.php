<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptParser\Tokenizer\LineGrouper;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class IndentationSniff implements TokenStreamSniffInterface
{

    private $useSpaces = true;

    private $indentPerLevel = 4;

    /**
     * Defines whether code inside conditions should be indented by one level.
     *
     * @var bool
     */
    private $indentConditions = false;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if (array_key_exists('useSpaces', $parameters)) {
            $this->useSpaces = $parameters['useSpaces'];
        }
        if (array_key_exists('indentPerLevel', $parameters)) {
            $this->indentPerLevel = $parameters['indentPerLevel'];
        }
        if (array_key_exists('indentConditions', $parameters)) {
            $this->indentConditions = $parameters['indentConditions'];
        }
    }

    /**
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TypoScriptLint\Linter\Report\File           $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration   $configuration
     * @return mixed
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration)
    {
        $indentCharacter  = $this->useSpaces ? ' ' : "\t";
        $tokensByLine     = new LineGrouper($tokens);
        $indentationLevel = 0;

        /** @var \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokensInLine */
        foreach ($tokensByLine->getLines() as $line => $tokensInLine) {
            if ($this->reduceIndentationLevel($tokensInLine)) {
                $indentationLevel--;
            }
            foreach ($tokensInLine as $key => $token) {
                if ($token->getType() === TokenInterface::TYPE_RIGHTVALUE_MULTILINE) {
                    unset($tokensInLine[$key]);
                    $tokensInLine = array_values($tokensInLine);
                }
            }
            $firstToken = count($tokensInLine) > 0 ? $tokensInLine[0] : null;

            // Skip empty lines.
            if (count($tokensInLine) == 1 && $firstToken->getType(
                ) === TokenInterface::TYPE_WHITESPACE && $firstToken->getValue() === "\n"
            ) {
                continue;
            }

            if ($indentationLevel === 0) {
                if ($tokensInLine[0]->getType() === TokenInterface::TYPE_WHITESPACE && strlen(
                        $tokensInLine[0]->getValue()
                    )
                ) {
                    $file->addWarning($this->createWarning($line, $indentationLevel, $tokensInLine[0]->getValue()));
                }
            } else {
                if ($tokensInLine[0]->getType() !== TokenInterface::TYPE_WHITESPACE) {
                    $file->addWarning($this->createWarning($line, $indentationLevel, ''));
                } else {
                    $expectedIndentationCharacterCount = $this->indentPerLevel * $indentationLevel;
                    $expectedIndentation               = str_repeat(
                        $indentCharacter,
                        $expectedIndentationCharacterCount
                    );

                    if ($tokensInLine[0]->getValue() !== $expectedIndentation) {
                        $file->addWarning($this->createWarning($line, $indentationLevel, $tokensInLine[0]->getValue()));
                    }
                }
            }

            if ($this->raiseIndentationLevel($tokensInLine)) {
                $indentationLevel++;
            }
        }
    }

    /**
     * Check whether indentation should be reduced by one level, for current line.
     *
     * Checks tokens in current line, and whether they will reduce the indentation by one.
     *
     * @param array $tokensInLine
     *
     * @return bool
     */
    private function reduceIndentationLevel(array $tokensInLine)
    {
        $raisingIndentation = [
            TokenInterface::TYPE_BRACE_CLOSE,
        ];

        if ($this->indentConditions) {
            $raisingIndentation[] = TokenInterface::TYPE_CONDITION_END;
        }

        foreach ($tokensInLine as $token) {
            if (in_array($token->getType(), $raisingIndentation)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check whether indentation should be raised by one level, for current line.
     *
     * Checks tokens in current line, and whether they will raise the indentation by one.
     *
     * @param array $tokensInLine
     *
     * @return bool
     */
    private function raiseIndentationLevel(array $tokensInLine)
    {
        $raisingIndentation = [
            TokenInterface::TYPE_BRACE_OPEN,
        ];

        if ($this->indentConditions) {
            $raisingIndentation[] = TokenInterface::TYPE_CONDITION;
        }

        foreach ($tokensInLine as $token) {
            if (in_array($token->getType(), $raisingIndentation)) {
                return true;
            }
        }

        return false;
    }

    private function createWarning($line, $expectedLevel, $actual)
    {
        $indentCharacterCount       = ($expectedLevel * $this->indentPerLevel);
        $indentCharacterDescription = ($this->useSpaces ? 'space' : 'tab') . (($indentCharacterCount == 1) ? '' : 's');

        $expectedMessage = "Expected indent of {$indentCharacterCount} {$indentCharacterDescription}.";

        return new Warning($line, strlen($actual), $expectedMessage, Warning::SEVERITY_WARNING, __CLASS__);
    }
}
