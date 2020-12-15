<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\ConfigNoCacheVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\SniffVisitor;

class ConfigNoCacheSniff extends AbstractSyntaxTreeSniff {
    /**
     * @return SniffVisitor
     */
    protected function buildVisitor(): SniffVisitor
    {
        return new ConfigNoCacheVisitor();
    }
}
