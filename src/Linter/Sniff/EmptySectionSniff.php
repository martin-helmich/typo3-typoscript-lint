<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\EmptySectionVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\SniffVisitor;

class EmptySectionSniff extends AbstractSyntaxTreeSniff
{
    protected function buildVisitor(): SniffVisitor
    {
        return new EmptySectionVisitor();
    }
}