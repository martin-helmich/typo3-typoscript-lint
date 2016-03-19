<?php
namespace Helmich\TypoScriptLint\Linter\Sniff\Inspection;

use Helmich\TypoScriptParser\Tokenizer\TokenInterface;

/**
 * Helper trait that contains common inspections for token streams
 *
 * @package Helmich\TypoScriptLint
 * @subpackage Linter\Sniff\Inspection
 */
trait TokenInspections
{
    /**
     * Tests whether a token is an operator
     *
     * @param TokenInterface $token
     * @return bool
     */
    private static function isOperator(TokenInterface $token)
    {
        return in_array($token->getType(), [
            TokenInterface::TYPE_OPERATOR_ASSIGNMENT,
            TokenInterface::TYPE_OPERATOR_COPY,
            TokenInterface::TYPE_OPERATOR_DELETE,
            TokenInterface::TYPE_OPERATOR_MODIFY,
            TokenInterface::TYPE_OPERATOR_REFERENCE,
        ]);
    }

    /**
     * Tests whether a token is a whitespace
     *
     * @param TokenInterface $token
     * @return bool
     */
    private static function isWhitespace(TokenInterface $token)
    {
        return $token->getType() === TokenInterface::TYPE_WHITESPACE;
    }

    /**
     * Tests whether a token is a whitespace of a given length
     *
     * @param TokenInterface $token
     * @param int            $length
     * @return bool
     */
    private static function isWhitespaceOfLength(TokenInterface $token, $length)
    {
        return static::isWhitespace($token) && strlen(trim($token->getValue(), "\n")) == $length;
    }
}