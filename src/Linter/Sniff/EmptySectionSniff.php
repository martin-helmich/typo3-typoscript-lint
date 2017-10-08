<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\EmptySectionVisitor;

class EmptySectionSniff extends AbstractSyntaxTreeSniff
{
    protected function buildVisitor()
    {
        return new EmptySectionVisitor();
    }
}