<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\Inspection\TokenInspections;
use Helmich\TypoScriptParser\Tokenizer\LineGrouper;
use Helmich\TypoScriptParser\Tokenizer\Token;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class OperatorWhitespaceSniff implements TokenStreamSniffInterface
{
    use TokenInspections;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @param TokenInterface[]    $tokens
     * @param File                $file
     * @param LinterConfiguration $configuration
     * @return TokenInterface[]
     *
     * @todo Too complex -- might need some refactoring; why the two nested loops!?
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration): array
    {
        $tokensByLine = new LineGrouper($tokens);
        $fixedTokens = [];

        /** @var TokenInterface[] $tokensInLine */
        foreach ($tokensByLine->getLines() as $line => $tokensInLine) {
            $fixedTokensInLine = $tokensInLine;
            $currentColumn = 1;
            $count = count($tokensInLine);
            for ($i = 0; $i < $count; $i++) {
                $currentColumn += mb_strlen($fixedTokensInLine[$i]->getValue());

                if (!($fixedTokensInLine[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER && isset($fixedTokensInLine[$i + 1]))) {
                    continue;
                }

                if (!self::isWhitespace($fixedTokensInLine[$i + 1])) {
                    $fixedTokensInLine = array_merge(
                        array_slice($fixedTokensInLine, 0, $i + 1),
                        [new Token(TokenInterface::TYPE_WHITESPACE, " ", $line, $currentColumn)],
                        array_slice($fixedTokensInLine, $i + 1)
                    );
                    $i ++;
                    $count ++;

                    $file->addIssue(new Issue(
                        $fixedTokensInLine[$i]->getLine(),
                        $fixedTokensInLine[$i]->getColumn(),
                        'No whitespace after object accessor.',
                        Issue::SEVERITY_WARNING,
                        __CLASS__,
                        true
                    ));
                } elseif (!self::isWhitespaceOfLength($fixedTokensInLine[$i + 1], 1)) {
                    $fixedTokensInLine[$i + 1] = new Token(TokenInterface::TYPE_WHITESPACE, " ", $line, $fixedTokensInLine[$i]->getColumn());

                    $file->addIssue(new Issue(
                        $fixedTokensInLine[$i]->getLine(),
                        $fixedTokensInLine[$i]->getColumn(),
                        'Accessor should be followed by single space.',
                        Issue::SEVERITY_WARNING,
                        __CLASS__,
                        true
                    ));
                }

                // Scan forward until we find the actual operator
                for ($j = 0; $j < $count && !self::isOperator($fixedTokensInLine[$j]); $j ++);

                if (isset($fixedTokensInLine[$j + 1]) && isset($fixedTokensInLine[$j + 2]) && self::isBinaryOperator($fixedTokensInLine[$j])) {
                    if (!self::isWhitespace($fixedTokensInLine[$j + 1])) {
                        $fixedTokensInLine = array_merge(
                            array_slice($fixedTokensInLine, 0, $j + 1),
                            [new Token(TokenInterface::TYPE_WHITESPACE, " ", $line, $currentColumn)],
                            array_slice($fixedTokensInLine, $j + 1)
                        );

                        $file->addIssue(new Issue(
                            $fixedTokensInLine[$j]->getLine(),
                            $fixedTokensInLine[$j]->getColumn(),
                            'No whitespace after operator.',
                            Issue::SEVERITY_WARNING,
                            __CLASS__,
                            true
                        ));
                    } elseif (!self::isWhitespaceOfLength($fixedTokensInLine[$j + 1], 1)) {
                        $fixedTokensInLine[$j + 1] = new Token(TokenInterface::TYPE_WHITESPACE, " ", $line, $fixedTokensInLine[$j]->getColumn());

                        $file->addIssue(new Issue(
                            $fixedTokensInLine[$j]->getLine(),
                            $fixedTokensInLine[$j]->getColumn(),
                            'Operator should be followed by single space.',
                            Issue::SEVERITY_WARNING,
                            __CLASS__,
                            true
                        ));
                    }
                }

                break;
            }

            $fixedTokens = array_merge($fixedTokens, $fixedTokensInLine);
        }

        return $fixedTokens;
    }
}
