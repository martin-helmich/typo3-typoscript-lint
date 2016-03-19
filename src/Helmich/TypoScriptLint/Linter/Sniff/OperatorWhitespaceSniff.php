<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptParser\Tokenizer\LineGrouper;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class OperatorWhitespaceSniff implements TokenStreamSniffInterface
{
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
     * @return void
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration)
    {
        $tokensByLine = new LineGrouper($tokens);

        /** @var TokenInterface[] $tokensInLine */
        foreach ($tokensByLine->getLines() as $line => $tokensInLine) {
            $count = count($tokensInLine);
            for ($i = 0; $i < $count; $i++) {
                if ($tokensInLine[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER && isset($tokensInLine[$i + 1])) {
                    if ($tokensInLine[$i + 1]->getType() !== TokenInterface::TYPE_WHITESPACE) {
                        $file->addWarning(new Warning(
                            $tokensInLine[$i]->getLine(),
                            null,
                            'No whitespace after object accessor.',
                            Warning::SEVERITY_WARNING,
                            __CLASS__
                        ));
                    } elseif (trim($tokensInLine[$i + 1]->getValue(), "\n") !== ' ') {
                        $file->addWarning(new Warning(
                            $tokensInLine[$i]->getLine(),
                            null,
                            'Accessor should be followed by single space.',
                            Warning::SEVERITY_WARNING,
                            __CLASS__
                        ));
                    }

                    // Scan forward until we find the actual operator
                    for ($j = 0; $j < $count && $tokensInLine[$j]->getType() !== TokenInterface::TYPE_OPERATOR_ASSIGNMENT; $j ++);

                    if (isset($tokensInLine[$j + 1])) {
                        if ($tokensInLine[$j + 1]->getType() !== TokenInterface::TYPE_WHITESPACE) {
                            $file->addWarning(new Warning(
                                $tokensInLine[$j]->getLine(),
                                null,
                                'No whitespace after operator.',
                                Warning::SEVERITY_WARNING,
                                __CLASS__
                            ));
                        } elseif (trim($tokensInLine[$j + 1]->getValue(), "\n") !== ' ') {
                            $file->addWarning(new Warning(
                                $tokensInLine[$j]->getLine(),
                                null,
                                'Operator should be followed by single space.',
                                Warning::SEVERITY_WARNING,
                                __CLASS__
                            ));
                        }
                    }
                }
            }
        }
    }
}
