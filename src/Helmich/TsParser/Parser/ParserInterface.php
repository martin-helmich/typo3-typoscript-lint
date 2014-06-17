<?php
namespace Helmich\TsParser\Parser;


interface ParserInterface
{



    /**
     * Parses a stream resource.
     *
     * This can be any kind of stream supported by PHP (e.g. a filename or a URL).
     *
     * @param string $stream The stream resource.
     * @return \Helmich\TsParser\Parser\AST\Statement[] The syntax tree.
     */
    public function parseStream($stream);



    /**
     * Parses a TypoScript string.
     *
     * @param string $string The string to parse.
     * @return \Helmich\TsParser\Parser\AST\Statement[] The syntax tree.
     */
    public function parseString($string);



    /**
     * Parses a token stream.
     *
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens The token stream to parse.
     * @return \Helmich\TsParser\Parser\AST\Statement[] The syntax tree.
     */
    public function parseTokens(array $tokens);
}