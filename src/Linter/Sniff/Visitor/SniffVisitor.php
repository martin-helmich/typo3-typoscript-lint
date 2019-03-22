<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Parser\Traverser\Visitor;

interface SniffVisitor extends Visitor
{
    /**
     * @return Issue[]
     */
    public function getIssues();
}