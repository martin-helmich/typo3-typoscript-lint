<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\Report\File;

interface TokenStreamSniffInterface extends SniffInterface
{


    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @param \Helmich\TsParser\Linter\Report\File         $file
     * @param \Helmich\TsParser\Linter\LinterConfiguration $configuration
     * @return mixed
     */
    public function sniff(array $tokens, File $file, LinterConfiguration $configuration);

}