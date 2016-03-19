<?php
namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Warning;
use Helmich\TypoScriptParser\Parser\Traverser\Visitor;

interface SniffVisitor extends Visitor
{
    /**
     * @return Warning[]
     */
    public function getWarnings();
}