<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;
use Helmich\TsParser\Linter\Sniff\Visitor\NestingConsistencyVisitor;
use Helmich\TsParser\Parser\Traverser\Traverser;

class NestingConsistencySniff implements SyntaxTreeSniffInterface
{



    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
    }



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[]     $statements
     * @param \Helmich\TsParser\Linter\Report\File         $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration $configuration
     * @return mixed
     */
    public function sniff(array $statements, File $file, LinterConfiguration $configuration)
    {
        $visitor = new NestingConsistencyVisitor();

        $traverser = new Traverser($statements);
        $traverser->addVisitor($visitor);
        $traverser->walk();

        foreach ($visitor->getWarnings() as $warning)
        {
            $file->addWarning($warning);
        }
    }
}