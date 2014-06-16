<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Report\Warning;
use Helmich\TsParser\Tokenizer\TokenInterface;
use Helmich\TsParser\Tokenizer\Tokenizer;

class DeadCodeSniff implements TokenStreamSniffInterface
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
        foreach ($tokens as $token)
        {
            if (!$token->getType() === TokenInterface::TYPE_COMMENT_ONELINE)
            {
                continue;
            }

            $commentContent = preg_replace(',^\s*#\s*,', '', $token->getValue());

            if (preg_match(Tokenizer::TOKEN_OPERATOR_LINE, $commentContent, $matches))
            {
                $warning = new Warning(
                    $token->getLine(),
                    0,
                    'Found commented code (' . $matches[0] . ').',
                    Warning::SEVERITY_INFO,
                    __CLASS__
                );
                $file->addWarning($warning);
            }
        }
    }
}