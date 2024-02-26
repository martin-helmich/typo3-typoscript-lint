<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\Inspection\TokenInspections;
use Helmich\TypoScriptParser\Tokenizer\LineGrouper;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class OperatorWhitespaceSniff implements TokenStreamSniffInterface
{
    use TokenInspections;

    public function __construct(array $parameters)
    {
    }

    /**
     * @param TokenInterface[] $tokens
     * @param File $file
     * @param LinterConfiguration $configuration
     *
     * @return void
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration): void
    {
        $tokensByLine = new LineGrouper($tokens);

        foreach ($tokensByLine->getLines() as $line => $tokensInLine) {
            $count = count($tokensInLine);
            for ($i = 0; $i < $count; $i++) {
                if (!($tokensInLine[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER
                    && isset($tokensInLine[$i + 1]))
                ) {
                    continue;
                }

                if (!self::isWhitespace($tokensInLine[$i + 1])) {
                    $file->addIssue(new Issue(
                        $tokensInLine[$i]->getLine(),
                        null,
                        'No whitespace after object accessor.',
                        Issue::SEVERITY_WARNING,
                        self::class
                    ));
                } elseif (!self::isWhitespaceOfLength($tokensInLine[$i + 1], 1)) {
                    $file->addIssue(new Issue(
                        $tokensInLine[$i]->getLine(),
                        null,
                        'Accessor should be followed by single space.',
                        Issue::SEVERITY_WARNING,
                        self::class
                    ));
                }

                // Scan forward until we find the actual operator
                for ($j = 0; $j < $count && !self::isOperator($tokensInLine[$j]); $j++) {
                    ;
                }

                if (isset($tokensInLine[$j + 1]) && isset($tokensInLine[$j + 2])
                    && self::isBinaryOperator($tokensInLine[$j])) {
                    if (!self::isWhitespace($tokensInLine[$j + 1])) {
                        $file->addIssue(new Issue(
                            $tokensInLine[$j]->getLine(),
                            null,
                            'No whitespace after operator.',
                            Issue::SEVERITY_WARNING,
                            self::class
                        ));
                    } elseif (!self::isWhitespaceOfLength($tokensInLine[$j + 1], 1)) {
                        $file->addIssue(new Issue(
                            $tokensInLine[$j]->getLine(),
                            null,
                            'Operator should be followed by single space.',
                            Issue::SEVERITY_WARNING,
                            self::class
                        ));
                    }
                }

                break;
            }
        }
    }
}
