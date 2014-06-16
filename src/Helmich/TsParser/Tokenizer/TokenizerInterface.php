<?php
namespace Helmich\TsParser\Tokenizer;


interface TokenizerInterface
{



    /**
     * @param string $inputString
     * @return \Helmich\TsParser\Tokenizer\TokenInterface[]
     */
    public function tokenizeString($inputString);



    /**
     * @param string $inputStream
     * @return \Helmich\TsParser\Tokenizer\TokenInterface[]
     */
    public function tokenizeStream($inputStream);

}