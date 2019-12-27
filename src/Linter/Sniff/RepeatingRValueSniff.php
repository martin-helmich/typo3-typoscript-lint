<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

class RepeatingRValueSniff implements TokenStreamSniffInterface
{

    const CONSTANT_EXPRESSION = ',\{\$[a-zA-Z0-9_\.]+\},';

    /** @var array<string, int> */
    private $knownRightValues = [];

    /** @var string[] */
    private $allowedRightValues = [];

    /** @var int */
    private $valueLengthThreshold = 8;

    /**
     * @param array $parameters
     * @psalm-param array{allowedRightValues: ?string[], valueLengthThreshold: ?int} $parameters
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function __construct(array $parameters)
    {
        if (isset($parameters["allowedRightValues"])) {
            $this->allowedRightValues = $parameters["allowedRightValues"];
        }

        if (isset($parameters["valueLengthThreshold"])) {
            $this->valueLengthThreshold = $parameters["valueLengthThreshold"];
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
        foreach ($tokens as $token) {
            $isRValue                   = $token->getType() === TokenInterface::TYPE_RIGHTVALUE;
            $valueIsLongerThanThreshold = strlen($token->getValue()) >= $this->valueLengthThreshold;

            if (!$isRValue || !$valueIsLongerThanThreshold) {
                continue;
            }

            if (preg_match(self::CONSTANT_EXPRESSION, $token->getValue())) {
                continue;
            }

            if (in_array($token->getValue(), $this->allowedRightValues)) {
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

        return $tokens;
    }
}
