<?php
namespace Helmich\TsParser\Parser\Printer;


use Helmich\TsParser\Parser\AST\ConditionalStatement;
use Helmich\TsParser\Parser\AST\DirectoryIncludeStatement;
use Helmich\TsParser\Parser\AST\FileIncludeStatement;
use Helmich\TsParser\Parser\AST\NestedAssignment;
use Helmich\TsParser\Parser\AST\Operator\Assignment;
use Helmich\TsParser\Parser\AST\Operator\Copy;
use Helmich\TsParser\Parser\AST\Operator\Delete;
use Helmich\TsParser\Parser\AST\Operator\Modification;
use Helmich\TsParser\Parser\AST\Operator\Reference;
use Symfony\Component\Console\Output\OutputInterface;

class PrettyPrinter implements ASTPrinterInterface
{



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[]          $statements
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    public function printStatements(array $statements, OutputInterface $output)
    {
        $this->printStatementList($statements, $output, 0);
    }



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[]          $statements
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int                                               $nesting
     * @return string
     */
    private function printStatementList(array $statements, OutputInterface $output, $nesting = 0)
    {
        foreach ($statements as $statement)
        {
            if ($statement instanceof NestedAssignment)
            {
                $this->printNestedAssignment($output, $nesting, $statement);
            }
            else if ($statement instanceof Assignment)
            {
                $output->writeln($this->getIndent($nesting) . $statement->object->relativeName . ' = ' . $statement->value->value);
            }
            else if ($statement instanceof Copy)
            {
                $output->writeln($this->getIndent($nesting) . $statement->object->relativeName . ' < ' . $statement->target->absoluteName);
            }
            else if ($statement instanceof Reference)
            {
                $output->writeln($this->getIndent($nesting) . $statement->object->relativeName . ' <= ' . $statement->target->absoluteName);
            }
            else if ($statement instanceof Delete)
            {
                $output->writeln($this->getIndent($nesting) . $statement->object->relativeName . ' >');
            }
            else if ($statement instanceof Modification)
            {
                $output->writeln(
                    $this->getIndent(
                        $nesting
                    ) . $statement->object->relativeName . ' := ' . $statement->call->method . '(' . $statement->call->arguments . ')'
                );
            }
            else if ($statement instanceof ConditionalStatement)
            {
                $this->printConditionalStatement($output, $nesting, $statement);
            }
            else if ($statement instanceof FileIncludeStatement)
            {
                $output->writeln('<INCLUDE_TYPOSCRIPT: source="FILE:' . $statement->filename . '">');
            }
            else if ($statement instanceof DirectoryIncludeStatement)
            {
                if ($statement->extension)
                {
                    $output->writeln(
                        '<INCLUDE_TYPOSCRIPT: source="DIR:' . $statement->directory . '" extension="' . $statement->extension . '">'
                    );
                }
                else
                {
                    $output->writeln('<INCLUDE_TYPOSCRIPT: source="DIR:' . $statement->directory . '">');
                }
            }
        }
    }



    private function getIndent($nesting)
    {
        return str_repeat('    ', $nesting);
    }



    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param                                                   $nesting
     * @param \Helmich\TsParser\Parser\AST\NestedAssignment     $statement
     */
    private function printNestedAssignment(OutputInterface $output, $nesting, NestedAssignment $statement)
    {
        $output->writeln($this->getIndent($nesting) . $statement->object->relativeName . ' {');
        $this->printStatementList($statement->statements, $output, $nesting + 1);
        $output->writeln($this->getIndent($nesting) . '}');
    }



    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int                                               $nesting
     * @param \Helmich\TsParser\Parser\AST\ConditionalStatement $statement
     */
    private function printConditionalStatement(OutputInterface $output, $nesting, $statement)
    {
        $output->writeln('');
        $output->writeln($statement->condition);
        $this->printStatementList($statement->ifStatements, $output, $nesting);

        if (count($statement->elseStatements) > 0)
        {
            $output->writeln('[else]');
            $this->printStatementList($statement->elseStatements, $output, $nesting);
        }

        $output->writeln('[global]');
    }
}