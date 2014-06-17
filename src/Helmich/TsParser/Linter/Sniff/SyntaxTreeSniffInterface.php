<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;

interface SyntaxTreeSniffInterface extends SniffInterface
{



    /**
     * @param \Helmich\TsParser\Parser\AST\Statement[]     $statements
     * @param \Helmich\TsParser\Linter\Report\File         $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration $configuration
     * @return mixed
     */
    public function sniff(array $statements, File $file, LinterConfiguration $configuration);

}