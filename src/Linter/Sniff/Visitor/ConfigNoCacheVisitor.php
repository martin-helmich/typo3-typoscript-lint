<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\ConfigNoCacheSniff;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

class ConfigNoCacheVisitor implements SniffVisitor
{
    /** @var Issue[] */
    private $issues = [];

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    public function enterTree(array $statements): void
    {
    }

    public function enterNode(Statement $statement): void
    {
        if (!$statement instanceof Assignment) {
            return;
        }
        if ($statement->object->relativeName !== 'no_cache'
            && substr($statement->object->relativeName, -9) !== '.no_cache') {
            return;
        }
        if ($statement->value->value !== '0') {
            $this->issues[] = new Issue(
                $statement->sourceLine,
                null,
                sprintf(
                    'Setting config.no_cache = 1 is discouraged as it is bad for performance. '
                    . 'Consider using USER_INT object instead. Found in path: %s',
                    $statement->object->absoluteName
                ),
                Issue::SEVERITY_WARNING,
                ConfigNoCacheSniff::class
            );
        }
    }

    public function exitNode(Statement $statement): void
    {
    }

    public function exitTree(array $statements): void
    {
    }
}
