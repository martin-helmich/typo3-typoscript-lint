<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class RepeatingRValueSniff implements TokenStreamSniffInterface
{

    const CONSTANT_EXPRESSION = ',\{\$[a-zA-Z0-9_\.]+\},';

    private $knownRightValues = [];

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
        foreach ($tokens as $token) {
            if ($token->getType() !== TokenInterface::TYPE_RIGHTVALUE || strlen($token->getValue()) < 8) {
                continue;
            }

            if (preg_match(self::CONSTANT_EXPRESSION, $token->getValue())) {
                continue;
            }

            if (!array_key_exists($token->getValue(), $this->knownRightValues)) {
                $this->knownRightValues[$token->getValue()] = 0;
            }

            $this->knownRightValues[$token->getValue()]++;

            if ($this->knownRightValues[$token->getValue()] > 1) {
                $file->addIssue(new Issue(
                    $token->getLine(),
                    null,
                    'Duplicated value "' . $token->getValue() . '". Consider extracting it into a constant.',
                    Issue::SEVERITY_WARNING,
                    __CLASS__
                ));
            }
        }
    }
}
