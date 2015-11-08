<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;


use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use Helmich\TypoScriptParser\Tokenizer\Tokenizer;

class DeadCodeSniff implements TokenStreamSniffInterface
{


    const ANNOTATION_COMMENT = '/^\s*([a-z0-9]+=(.*?))(;\s*[a-z0-9]+=(.*?))*\s*$/';


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
     * @return mixed
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration)
    {
        foreach ($tokens as $token)
        {
            if (!($token->getType() === TokenInterface::TYPE_COMMENT_ONELINE || $token->getType() === TokenInterface::TYPE_COMMENT_MULTILINE))
            {
                continue;
            }

            $commentContent = preg_replace(',^\s*(#|/\*|/)\s*,', '', $token->getValue());

            if (preg_match(static::ANNOTATION_COMMENT, $commentContent))
            {
                continue;
            }
            else if (preg_match(Tokenizer::TOKEN_OPERATOR_LINE, $commentContent, $matches))
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
