<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;

interface TokenStreamSniffInterface extends SniffInterface
{

    /**
     * @param \Helmich\TypoScriptParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TypoScriptLint\Linter\Report\File           $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration   $configuration
     * @return void
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration);
}
