<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\NestingConsistencyVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\SniffVisitor;

class NestingConsistencySniff extends AbstractSyntaxTreeSniff
{
    /**
     * @return SniffVisitor
     */
    protected function buildVisitor()
    {
        return new NestingConsistencyVisitor();
    }
}
