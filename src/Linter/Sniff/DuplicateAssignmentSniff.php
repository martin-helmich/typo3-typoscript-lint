<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\DuplicateAssignmentVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\SniffVisitor;

class DuplicateAssignmentSniff extends AbstractSyntaxTreeSniff
{
    /**
     * @return SniffVisitor
     */
    protected function buildVisitor()
    {
        return new DuplicateAssignmentVisitor();
    }
}
