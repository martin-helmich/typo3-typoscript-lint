<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\Inspection\TokenInspections;
use Helmich\TypoScriptParser\Tokenizer\LineGrouper;
use Helmich\TypoScriptParser\Tokenizer\Token;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class IndentationSniff implements TokenStreamSniffInterface
{
    use TokenInspections;

    /** @var bool */
    private $useSpaces = true;

    /** @var int */
    private $indentPerLevel = 4;

    /**
     * Defines whether code inside conditions should be indented by one level.
     *
     * @var bool
     */
    private $indentConditions = false;

    /**
     * Track whether we are inside a condition.
     *
     * @var bool
     */
    private $insideCondition = false;

    /**
     * @param array $parameters
     * @psalm-param array{useSpaces: ?bool, indentPerLevel: ?int, indentCondition: ?bool} $parameters
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function __construct(array $parameters)
    {
        if (isset($parameters['useSpaces'])) {
            $this->useSpaces = $parameters['useSpaces'];
        }
        if (isset($parameters['indentPerLevel'])) {
            $this->indentPerLevel = $parameters['indentPerLevel'];
        }
        if (isset($parameters['indentConditions'])) {
            $this->indentConditions = $parameters['indentConditions'];
        }
    }

    /**
     * @param TokenInterface[]    $tokens
     * @param File                $file
     * @param LinterConfiguration $configuration
     * @return TokenInterface[]
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration): array
    {
        $fixedTokens = [];
        $indentCharacter  = $this->useSpaces ? ' ' : "\t";
        $tokensByLine     = new LineGrouper($tokens);
        $indentationLevel = 0;

        /** @var TokenInterface[] $tokensInLine */
        foreach ($tokensByLine->getLines() as $line => $tokensInLine) {
            $originalTokensInLine = $tokensInLine;
            $fixedTokensInLine    = $tokensInLine;
            $indentationLevel     = $this->reduceIndentationLevel($indentationLevel, $tokensInLine);

            $expectedIndentationCharacterCount = $this->indentPerLevel * $indentationLevel;
            $expectedIndentation               = str_repeat(
                $indentCharacter,
                $expectedIndentationCharacterCount
            );

            $tokensInLine = array_values(array_filter($tokensInLine, function(TokenInterface $token): bool {
                return $token->getType() !== TokenInterface::TYPE_RIGHTVALUE_MULTILINE;
            }));

            // Skip empty lines and conditions inside conditions.
            if ($this->isEmptyLine($tokensInLine) || $this->insideCondition && $tokensInLine[0] !== TokenInterface::TYPE_CONDITION && !self::isWhitespace($tokensInLine[0])) {
                $fixedTokens = array_merge($fixedTokens, $originalTokensInLine);
                continue;
            }

            $line = (int)$line;

            if ($indentationLevel === 0 && self::isWhitespace($tokensInLine[0]) && strlen($tokensInLine[0]->getValue())) {
                $fixedTokensInLine = array_slice($originalTokensInLine, 1);
                $file->addIssue($this->createIssue($line, $indentationLevel, $tokensInLine[0]->getValue(), true));
            } elseif ($indentationLevel > 0) {
                if (!self::isWhitespace($tokensInLine[0])) {
                    $fixedTokensInLine = array_merge(
                        [new Token(TokenInterface::TYPE_WHITESPACE, $expectedIndentation, $line, 1)],
                        $originalTokensInLine
                    );
                    $file->addIssue($this->createIssue($line, $indentationLevel, '', true));
                } elseif ($tokensInLine[0]->getValue() !== $expectedIndentation) {
                    $fixedTokensInLine = array_merge(
                        [new Token(TokenInterface::TYPE_WHITESPACE, $expectedIndentation, $line, 1)],
                        array_slice($originalTokensInLine, 1)
                    );
                    $file->addIssue($this->createIssue($line, $indentationLevel, $tokensInLine[0]->getValue(), true));
                }
            }

            $indentationLevel = $this->raiseIndentationLevel($indentationLevel, $tokensInLine);
            $fixedTokens = array_merge($fixedTokens, $fixedTokensInLine);
        }

        return $fixedTokens;
    }

    /**
     * Checks if a stream of tokens is an empty line.
     *
     * @param TokenInterface[] $tokensInLine
     * @return bool
     */
    private function isEmptyLine(array $tokensInLine): bool
    {
        if (count($tokensInLine) > 1) {
            return false;
        }

        $firstToken = $tokensInLine[0];
        return $firstToken->getType() === TokenInterface::TYPE_WHITESPACE && $firstToken->getValue() === "\n";
    }

    /**
     * Check whether indentation should be reduced by one level, for current line.
     *
     * Checks tokens in current line, and whether they will reduce the indentation by one.
     *
     * @param int $indentationLevel The current indentation level
     * @param TokenInterface[] $tokensInLine
     * @return int The new indentation level
     */
    private function reduceIndentationLevel(int $indentationLevel, array $tokensInLine): int
    {
        $raisingIndentation = [
            TokenInterface::TYPE_BRACE_CLOSE,
        ];

        if ($this->indentConditions) {
            $raisingIndentation[] = TokenInterface::TYPE_CONDITION_END;
        }

        foreach ($tokensInLine as $token) {
            if (in_array($token->getType(), $raisingIndentation)) {
                if ($token->getType() === TokenInterface::TYPE_CONDITION_END) {
                    $this->insideCondition = false;
                }
                return max($indentationLevel - 1, 0);
            }
        }

        return $indentationLevel;
    }

    /**
     * Check whether indentation should be raised by one level, for current line.
     *
     * Checks tokens in current line, and whether they will raise the indentation by one.
     *
     * @param int $indentationLevel The current indentation level
     * @param TokenInterface[] $tokensInLine
     * @return int The new indentation level
     */
    private function raiseIndentationLevel(int $indentationLevel, array $tokensInLine): int
    {
        $raisingIndentation = [
            TokenInterface::TYPE_BRACE_OPEN,
        ];

        if ($this->indentConditions && $this->insideCondition === false) {
            $raisingIndentation[] = TokenInterface::TYPE_CONDITION;
        }

        foreach ($tokensInLine as $token) {
            if (in_array($token->getType(), $raisingIndentation)) {
                if ($token->getType() === TokenInterface::TYPE_CONDITION) {
                    $this->insideCondition = true;
                }
                return $indentationLevel + 1;
            }
        }

        return $indentationLevel;
    }

    private function createIssue(int $line, int $expectedLevel, string $actual, bool $fixable): Issue
    {
        $indentCharacterCount       = ($expectedLevel * $this->indentPerLevel);
        $indentCharacterDescription = ($this->useSpaces ? 'space' : 'tab') . (($indentCharacterCount == 1) ? '' : 's');

        $expectedMessage = "Expected indent of {$indentCharacterCount} {$indentCharacterDescription}.";

        return new Issue($line, strlen($actual), $expectedMessage, Issue::SEVERITY_WARNING, __CLASS__, $fixable);
    }
}
