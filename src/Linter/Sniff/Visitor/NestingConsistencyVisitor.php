<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Sniff\NestingConsistencySniff;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

class NestingConsistencyVisitor implements SniffVisitor
{

    /** @var Issue[] */
    private array $issues = [];

    private int $commonPathPrefixThreshold;

    public function __construct(int $commonPathPrefixThreshold = 1)
    {
        $this->commonPathPrefixThreshold = $commonPathPrefixThreshold;
    }

    /**
     * @return Issue[]
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    public function enterTree(array $statements): void
    {
        $this->walkStatementList($statements);
    }

    public function enterNode(Statement $statement): void
    {
        if ($statement instanceof NestedAssignment) {
            $this->walkStatementList($statement->statements);
        } elseif ($statement instanceof ConditionalStatement) {
            $this->walkStatementList($statement->ifStatements);
            $this->walkStatementList($statement->elseStatements);
        }
    }

    public function exitNode(Statement $statement): void
    {
    }

    public function exitTree(array $statements): void
    {
    }

    /**
     * @param Statement[] $statements
     */
    private function walkStatementList(array $statements): void
    {
        list($knownObjectPaths, $knownNestedObjectPaths) = $this->getAssignedObjectPathsFromStatementList($statements);

        // Step 2: Discover all plain assignments and determine whether any of them
        // can be moved within one of the nested assignments.
        foreach ($statements as $statement) {
            if (!isset($statement->object)) {
                continue;
            }
            
            $commonPrefixWarnings = [];
            foreach ($this->getParentObjectPathsForObjectPath($statement->object->relativeName) as $possibleObjectPath) {
                if (isset($knownNestedObjectPaths[$possibleObjectPath])) {
                    $this->issues[] = new Issue(
                        $statement->sourceLine,
                        null,
                        sprintf(
                            'Operation on value "%s", altough nested statement for path "%s" exists at line %d.',
                            $statement->object->relativeName,
                            $possibleObjectPath,
                            $knownNestedObjectPaths[$possibleObjectPath]
                        ),
                        Issue::SEVERITY_WARNING,
                        NestingConsistencySniff::class
                    );
                }

                $assignmentsWithCommonPrefix = [];

                foreach ($knownObjectPaths as $key => $line) {
                    $key = "" . $key;

                    if ($key !== $statement->object->relativeName && strpos($key, $possibleObjectPath . '.') === 0) {
                        if (!isset($assignmentsWithCommonPrefix[$key])) {
                            $assignmentsWithCommonPrefix[$key] = [];
                        }
                        $assignmentsWithCommonPrefix[$possibleObjectPath][] = [$key, $line];
                    }
                }

                foreach ($assignmentsWithCommonPrefix as $commonPrefix => $lines) {
                    if (count($lines) < $this->commonPathPrefixThreshold) {
                        continue;
                    }

                    $descr = [];
                    foreach ($lines as $l) {
                        $descr[] = sprintf('"%s" in line %d', $l[0], $l[1]);
                    }

                    $commonPrefixWarnings[$commonPrefix] = new Issue(
                        $statement->sourceLine,
                        null,
                        sprintf(
                            'Common path prefix "%s" with %s to %s. Consider merging them into a nested statement.',
                            $commonPrefix,
                            count($lines) === 1 ? 'operation' : 'operations',
                            implode(", ", $descr)
                        ),
                        Issue::SEVERITY_WARNING,
                        NestingConsistencySniff::class
                    );
                }
            }
            $this->issues = array_merge($this->issues, array_values($commonPrefixWarnings));
        }
    }

    /**
     * @param string $objectPath
     * @return string[]
     */
    private function getParentObjectPathsForObjectPath(string $objectPath): array
    {
        $components = preg_split('/(?<!\\\)\./', $objectPath);
        $paths      = [];
        for ($i = 1; $i < count($components); $i++) {
            $possibleObjectPath = implode('.', array_slice($components, 0, $i));
            $paths[]            = $possibleObjectPath;
        }
        return $paths;
    }

    /**
     * @param Statement[] $statements
     *
     * @return int[][]
     */
    private function getAssignedObjectPathsFromStatementList(array $statements): array
    {
        $knownObjectPaths       = [];
        $knownNestedObjectPaths = [];

        // Step 1: Discover all nested object assignment statements.
        foreach ($statements as $statement) {
            if (!isset($statement->object)) {
                continue;
            }
            
            $knownObjectPaths[$statement->object->relativeName] = $statement->sourceLine;
            if ($statement instanceof NestedAssignment) {
                if (isset($knownNestedObjectPaths[$statement->object->relativeName])) {
                    $this->issues[] = new Issue(
                        $statement->sourceLine,
                        null,
                        sprintf(
                            'Multiple nested statements for object path "%s". Consider merging them into one statement.',
                            $statement->object->relativeName
                        ),
                        Issue::SEVERITY_WARNING,
                        NestingConsistencySniff::class
                    );
                } else {
                    $knownNestedObjectPaths[$statement->object->relativeName] = $statement->sourceLine;
                }
            }
        }

        return [$knownObjectPaths, $knownNestedObjectPaths];
    }
}
