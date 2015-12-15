<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;

interface SyntaxTreeSniffInterface extends SniffInterface
{

    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement[]   $statements
     * @param \Helmich\TypoScriptLint\Linter\Report\File         $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration $configuration
     * @return void
     */
    public function sniff(array $statements, File $file, LinterConfiguration $configuration);
}
