<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Warning;
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
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TypoScriptLint\Linter\Report\File           $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration   $configuration
     * @return void
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration)
    {
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER) {
                if (isset($tokens[$i + 1])) {
                    if ($tokens[$i + 1]->getType() !== TokenInterface::TYPE_WHITESPACE) {
                        $warning = new Warning(
                            $tokens[$i]->getLine(),
                            null,
                            'No whitespace after object accessor.',
                            Warning::SEVERITY_WARNING,
                            __CLASS__
                        );

                        $file->addWarning($warning);
                    } else {
                        if (trim($tokens[$i + 1]->getValue(), "\n") !== ' ') {
                            $warning = new Warning(
                                $tokens[$i]->getLine(),
                                null,
                                'Operator should be followed by single space.',
                                Warning::SEVERITY_WARNING,
                                __CLASS__
                            );

                            $file->addWarning($warning);
                        }
                    }
                }
            }
        }
    }
}
