<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Report\Warning;
use Helmich\TsParser\Tokenizer\TokenInterface;

class OperatorWhitespaceSniff implements TokenStreamSniffInterface
{



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
        $count = count($tokens);
        for ($i = 0; $i < $count; $i++)
        {
            if ($tokens[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER)
            {
                if (isset($tokens[$i + 1]))
                {
                    if ($tokens[$i + 1]->getType() !== TokenInterface::TYPE_WHITESPACE)
                    {
                        $warning = new Warning(
                            $tokens[$i]->getLine(),
                            NULL,
                            'No whitespace after object accessor.',
                            Warning::SEVERITY_WARNING,
                            __CLASS__
                        );

                        $file->addWarning($warning);
                    }
                    else
                    {
                        if (trim($tokens[$i+1]->getValue(), "\n") !== ' ')
                        {
                            $warning = new Warning(
                                $tokens[$i]->getLine(),
                                NULL,
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