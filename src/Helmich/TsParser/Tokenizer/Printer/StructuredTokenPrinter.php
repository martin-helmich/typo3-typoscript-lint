<?php
namespace Helmich\TsParser\Tokenizer\Printer;


use Symfony\Component\Yaml\Yaml;

class StructuredTokenPrinter implements TokenPrinterInterface
{



    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @return string
     */
    public function printTokenStream(array $tokens)
    {
        $content = '';

        foreach ($tokens as $token)
        {
            $content .= sprintf("%20s %s\n", $token->getType(), Yaml::dump($token->getValue()));
        }

        return $content;
    }
}