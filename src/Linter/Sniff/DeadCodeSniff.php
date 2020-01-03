<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Parser\Parser;
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
     * @param TokenInterface[]    $tokens
     * @param File                $file
     * @param LinterConfiguration $configuration
     * @return TokenInterface[]
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration): array
    {
        $fixedTokens = $tokens;

        //foreach ($tokens as $i => $token) {
        $count = count($tokens);
        for ($i = 0; $i < $count; $i ++) {
            $token = $tokens[$i];

            if (!($token->getType() === TokenInterface::TYPE_COMMENT_ONELINE
                || $token->getType() === TokenInterface::TYPE_COMMENT_MULTILINE)) {
                continue;
            }

            $commentContent = preg_replace(',^\s*(#|/\*|/)\s*,', '', $token->getValue());

            if (preg_match(static::ANNOTATION_COMMENT, $commentContent)) {
                continue;
            } else if (preg_match(Tokenizer::TOKEN_OPERATOR_LINE, $commentContent, $matches)) {
                try {
                    $parser = new Parser(new Tokenizer());
                    $parser->parseString($commentContent);

                    unset($fixedTokens[$i]);
                    for ($j = $i - 1; $j >= 0 && $tokens[$j]->getLine() === $tokens[$i]->getLine(); $j --) {
                        if ($tokens[$j]->getType() === TokenInterface::TYPE_WHITESPACE) {
                            unset($fixedTokens[$j]);
                        }
                    }
                    for ($j = $i + 1; $j < $count && $tokens[$j]->getLine() === $tokens[$i]->getLine(); $j ++) {
                        if ($tokens[$j]->getType() === TokenInterface::TYPE_WHITESPACE) {
                            unset($fixedTokens[$j]);
                        }
                    }

                    $file->addIssue(new Issue(
                        $token->getLine(),
                        0,
                        'Found commented code (' . $matches[0] . ').',
                        Issue::SEVERITY_INFO,
                        __CLASS__,
                        true
                    ));
                } catch (\Exception $e) {
                    // pass
                }
            }
        }

        return array_values($fixedTokens);
    }
}
