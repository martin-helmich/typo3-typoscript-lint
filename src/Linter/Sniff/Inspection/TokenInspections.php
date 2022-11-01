<?php declare(strict_types=1);

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
     *
     * @return bool
     */
    private static function isOperator(TokenInterface $token): bool
    {
        return static::isUnaryOperator($token) || static::isBinaryOperator($token);
    }

    /**
     * Tests whether a token is a unary operator
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    private static function isUnaryOperator(TokenInterface $token): bool
    {
        return $token->getType() === TokenInterface::TYPE_OPERATOR_DELETE;
    }

    /**
     * Tests whether a token is a binary operator
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    private static function isBinaryOperator(TokenInterface $token): bool
    {
        return in_array($token->getType(), [
            TokenInterface::TYPE_OPERATOR_ASSIGNMENT,
            TokenInterface::TYPE_OPERATOR_COPY,
            TokenInterface::TYPE_OPERATOR_MODIFY,
            TokenInterface::TYPE_OPERATOR_REFERENCE,
        ]);
    }

    /**
     * Tests whether a token is a whitespace
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    private static function isWhitespace(TokenInterface $token): bool
    {
        return $token->getType() === TokenInterface::TYPE_WHITESPACE;
    }

    /**
     * Tests whether a token is a whitespace of a given length
     *
     * @param TokenInterface $token
     * @param int $length
     *
     * @return bool
     */
    private static function isWhitespaceOfLength(TokenInterface $token, int $length): bool
    {
        return static::isWhitespace($token) && strlen(trim($token->getValue(), "\n")) == $length;
    }

    /**
     * Tests whether a token is a condition
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    private static function isCondition(TokenInterface $token): bool
    {
        return $token->getType() === TokenInterface::TYPE_CONDITION
            || $token->getType() === TokenInterface::TYPE_CONDITION_ELSE;
    }
}
