<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Report\Warning;
use Helmich\TsParser\Tokenizer\TokenInterface;

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
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TsParser\Linter\Report\File         $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration $configuration
     * @return mixed
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration)
    {
        foreach ($tokens as $token)
        {
            if ($token->getType() !== TokenInterface::TYPE_RIGHTVALUE || strlen($token->getValue()) < 8)
            {
                continue;
            }

            if (preg_match(self::CONSTANT_EXPRESSION, $token->getValue()))
            {
                continue;
            }

            if (!array_key_exists($token->getValue(), $this->knownRightValues))
            {
                $this->knownRightValues[$token->getValue()] = 0;
            }

            $this->knownRightValues[$token->getValue()]++;

            if ($this->knownRightValues[$token->getValue()] > 1)
            {
                $warning = new Warning(
                    $token->getLine(),
                    NULL,
                    'Duplicated value "' . $token->getValue() . '". Consider extracting it into a constant.',
                    Warning::SEVERITY_WARNING,
                    __CLASS__
                );
                $file->addWarning($warning);
            }
        }
    }
}