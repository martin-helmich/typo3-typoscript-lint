<?php
namespace Helmich\TsParser\Tokenizer\Printer;


interface TokenPrinterInterface
{



    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @return string
     */
    public function printTokenStream(array $tokens);

} 