<?php
namespace Helmich\TypoScriptLint\Linter\Sniff\Visitor;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;

class NestingConsistencyVisitor implements SniffVisitor
{

    /** @var Issue[] */
    private $issues = [];

    /**
     * @return Issue[]
     */
    public function getIssues()
    {
        return $this->issues;
    }

    public function enterTree(array $statements)
    {
        $this->walkStatementList($statements);
    }

    public function enterNode(Statement $statement)
    {
        if ($statement instanceof NestedAssignment) {
            $this->walkStatementList($statement->statements);
        } else if ($statement instanceof ConditionalStatement) {
            $this->walkStatementList($statement->ifStatements);
            $this->walkStatementList($statement->elseStatements);
        }
    }

    public function exitNode(Statement $statement)
    {
    }

    public function exitTree(array $statements)
    {
    }

    /**
     * @param Statement[] $statements
     */
    private function walkStatementList(array $statements)
    {
        list($knownObjectPaths, $knownNestedObjectPaths) = $this->getAssignedObjectPathsFromStatementList($statements);

        // Step 2: Discover all plain assignments and determine whether any of them
        // can be moved within one of the nested assignments.
        foreach ($statements as $statement) {
            if ($statement instanceof Assignment || $statement instanceof NestedAssignment) {
                $commonPrefixWarnings = [];
                foreach ($this->getParentObjectPathsForObjectPath(
                    $statement->object->relativeName
                ) as $possibleObjectPath) {
                    if (isset($knownNestedObjectPaths[$possibleObjectPath])) {
                        $this->issues[] = new Issue(
                            $statement->sourceLine,
                            null,
                            sprintf(
                                'Assignment to value "%s", altough nested statement for path "%s" exists at line %d.',
                                $statement->object->relativeName,
                                $possibleObjectPath,
                                $knownNestedObjectPaths[$possibleObjectPath]
                            ),
                            Issue::SEVERITY_WARNING,
                            'Helmich\TypoScriptLint\Linter\Sniff\NestingConsistencySniff'
                        );
                    }

                    foreach ($knownObjectPaths as $key => $line) {
                        if ($key !== $statement->object->relativeName && strpos(
                                $key,
                                $possibleObjectPath . '.'
                            ) === 0
                        ) {
                            $commonPrefixWarnings[$key] = new Issue(
                                $statement->sourceLine,
                                null,
                                sprintf(
                                    'Common path prefix with assignment to "%s" in line %d. Consider merging them into a nested assignment.',
                                    $key,
                                    $line
                                ),
                                Issue::SEVERITY_WARNING,
                                'Helmich\TypoScriptLint\Linter\Sniff\NestingConsistencySniff'
                            );
                        }
                    }
                }
                $this->issues = array_merge($this->issues, array_values($commonPrefixWarnings));
            }
        }
    }

    /**
     * @param string $objectPath
     * @return array
     */
    private function getParentObjectPathsForObjectPath($objectPath)
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
     * @param array $statements
     * @return array
     */
    private function getAssignedObjectPathsFromStatementList(array $statements)
    {
        $knownObjectPaths = [];
        $knownNestedObjectPaths = [];

        // Step 1: Discover all nested object assignment statements.
        foreach ($statements as $statement) {
            if ($statement instanceof Assignment || $statement instanceof NestedAssignment) {
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
                            'Helmich\TypoScriptLint\Linter\Sniff\NestingConsistencySniff'
                        );
                    } else {
                        $knownNestedObjectPaths[$statement->object->relativeName] = $statement->sourceLine;
                    }
                }
            }
        }

        return array($knownObjectPaths, $knownNestedObjectPaths);
    }
}
