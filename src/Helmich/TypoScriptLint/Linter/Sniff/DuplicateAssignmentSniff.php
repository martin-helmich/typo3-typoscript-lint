<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\DuplicateAssignmentVisitor;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;

class DuplicateAssignmentSniff implements SyntaxTreeSniffInterface
{

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\Statement[]   $statements
     * @param \Helmich\TypoScriptLint\Linter\Report\File         $file
     * @param \Helmich\TypoScriptLint\Linter\LinterConfiguration $configuration
     * @return mixed
     */
    public function sniff(array $statements, File $file, LinterConfiguration $configuration)
    {
        $visitor = new DuplicateAssignmentVisitor();

        $traverser = new Traverser($statements);
        $traverser->addVisitor($visitor);
        $traverser->walk();

        foreach ($visitor->getWarnings() as $warning) {
            $file->addWarning($warning);
        }
    }
}