<?php
namespace Helmich\TsParser\Parser;


use Helmich\TsParser\Parser\AST\ConditionalStatement;
use Helmich\TsParser\Parser\AST\DirectoryIncludeStatement;
use Helmich\TsParser\Parser\AST\FileIncludeStatement;
use Helmich\TsParser\Parser\AST\NestedAssignment;
use Helmich\TsParser\Parser\AST\ObjectPath;
use Helmich\TsParser\Parser\AST\Operator\Assignment;
use Helmich\TsParser\Parser\AST\Operator\Copy;
use Helmich\TsParser\Parser\AST\Operator\Delete;
use Helmich\TsParser\Parser\AST\Operator\Modification;
use Helmich\TsParser\Parser\AST\Operator\ModificationCall;
use Helmich\TsParser\Parser\AST\Operator\ObjectCreation;
use Helmich\TsParser\Parser\AST\Operator\Reference;
use Helmich\TsParser\Parser\AST\Scalar;
use Helmich\TsParser\Tokenizer\Token;
use Helmich\TsParser\Tokenizer\TokenInterface;
use Helmich\TsParser\Tokenizer\Tokenizer;
use Helmich\TsParser\Tokenizer\TokenizerInterface;

class Parser implements ParserInterface
{



    /** @var \Helmich\TsParser\Tokenizer\TokenizerInterface */
    private $tokenizer;



    public function __construct(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }



    /**
     * Parses a stream resource.
     *
     * This can be any kind of stream supported by PHP (e.g. a filename or a URL).
     *
     * @param string $stream The stream resource.
     * @return \Helmich\TsParser\Parser\AST\Statement[] The syntax tree.
     */
    public function parseStream($stream)
    {
        $content = file_get_contents($stream);
        return $this->parseString($content);
    }



    /**
     * Parses a TypoScript string.
     *
     * @param string $content The string to parse.
     * @return \Helmich\TsParser\Parser\AST\Statement[] The syntax tree.
     */
    public function parseString($content)
    {
        $tokens = $this->tokenizer->tokenizeString($content);
        return $this->parseTokens($tokens);
    }



    /**
     * Parses a token stream.
     *
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens The token stream to parse.
     * @return \Helmich\TsParser\Parser\AST\Statement[] The syntax tree.
     */
    public function parseTokens(array $tokens)
    {
        $statements = [];
        $tokens     = $this->filterTokenStream($tokens);

        $count = count($tokens);

        for ($i = 0; $i < $count; $i++)
        {
            if ($tokens[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER)
            {
                $objectPath = new ObjectPath($tokens[$i]->getValue(), $tokens[$i]->getValue());
                if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_BRACE_OPEN)
                {
                    $i += 2;
                    $statements[] = $this->parseNestedStatements($objectPath, $tokens, $i);
                }
            }

            $this->parseToken($tokens, $i, $statements, NULL);
        }

        return $statements;
    }



    /**
     * @param \Helmich\TsParser\Parser\AST\ObjectPath      $parentObject
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @param                                              $i
     * @throws ParseError
     * @return \Helmich\TsParser\Parser\AST\NestedAssignment
     */
    private function parseNestedStatements(ObjectPath $parentObject, array $tokens, &$i)
    {
        $statements = [];
        $count      = count($tokens);

        for (; $i < $count; $i++)
        {
            if ($tokens[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER)
            {
                $objectPath = new ObjectPath($parentObject->absoluteName . '.' . $tokens[$i]->getValue(), $tokens[$i]->getValue());
                if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_BRACE_OPEN)
                {
                    $i += 2;
                    $statements[] = $this->parseNestedStatements($objectPath, $tokens, $i);
                    continue;
                }
            }

            $this->parseToken($tokens, $i, $statements, $parentObject);

            if ($tokens[$i]->getType() === TokenInterface::TYPE_BRACE_CLOSE)
            {
                $statement = new NestedAssignment($parentObject, $statements, $tokens[$i]->getLine());
                $i++;
                return $statement;
            }
        }

        throw new ParseError('Unterminated nested statement!');
    }



    /**
     * @param \Helmich\TsParser\Parser\AST\ObjectPath      $context
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @param int                                          $i
     * @param \Helmich\TsParser\Parser\AST\Statement[]     $statements
     * @throws ParseError
     * @return \Helmich\TsParser\Parser\AST\NestedAssignment
     */
    private function parseToken(array $tokens, &$i, array &$statements, ObjectPath $context = NULL)
    {
        if ($tokens[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER)
        {
            $objectPath = $context
                ? new ObjectPath($context->absoluteName . '.' . $tokens[$i]->getValue(), $tokens[$i]->getValue())
                : new ObjectPath($tokens[$i]->getValue(), $tokens[$i]->getValue());

            if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_OPERATOR_ASSIGNMENT)
            {
                if ($tokens[$i + 2]->getType() === TokenInterface::TYPE_OBJECT_CONSTRUCTOR)
                {
                    $statements[] = new ObjectCreation($objectPath, new Scalar($tokens[$i + 2]->getValue()), $tokens[$i + 2]->getLine());
                    $i += 2;
                }
                elseif ($tokens[$i + 2]->getType() === TokenInterface::TYPE_RIGHTVALUE)
                {
                    $statements[] = new Assignment($objectPath, new Scalar($tokens[$i + 2]->getValue()), $tokens[$i + 2]->getLine());
                    $i += 2;
                }
                elseif ($tokens[$i + 2]->getType() === TokenInterface::TYPE_WHITESPACE)
                {
                    $statements[] = new Assignment($objectPath, new Scalar(''), $tokens[$i]->getLine());
                    $i += 1;
                }
            }
            else if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_OPERATOR_COPY
                || $tokens[$i + 1]->getType() === TokenInterface::TYPE_OPERATOR_REFERENCE
            )
            {
                $targetToken = $tokens[$i + 2];
                $this->validateCopyOperatorRightValue($targetToken);

                if ($targetToken->getValue()[0] === '.')
                {
                    $absolutePath = $context ? "{$context->absoluteName}{$targetToken->getValue()}" : $targetToken->getValue();
                }
                else
                {
                    $absolutePath = $targetToken->getValue();
                }

                $target = new ObjectPath($absolutePath, $targetToken->getValue());

                if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_OPERATOR_COPY)
                {
                    $statements[] = new Copy($objectPath, $target, $tokens[$i+1]->getLine());
                }
                else
                {
                    $statements[] = new Reference($objectPath, $target, $tokens[$i+1]->getLine());
                }
                $i += 2;
            }
            else if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_OPERATOR_MODIFY)
            {
                $this->validateModifyOperatorRightValue($tokens[$i + 2]);

                preg_match(Tokenizer::TOKEN_OBJECT_MODIFIER, $tokens[$i + 2]->getValue(), $matches);

                $call         = new ModificationCall($matches['name'], $matches['arguments']);
                $statements[] = new Modification($objectPath, $call, $tokens[$i+2]->getLine());

                $i += 2;
            }
            else if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_OPERATOR_DELETE)
            {
                if ($tokens[$i + 2]->getType() !== TokenInterface::TYPE_WHITESPACE)
                {
                    throw new ParseError(
                        'Unexpected token ' . $tokens[$i + 2]->getType() . ' after delete operator (expected line break).',
                        1403011201,
                        $tokens[$i]->getLine()
                    );
                }

                $statements[] = new Delete($objectPath, $tokens[$i+1]->getLine());
                $i += 1;
            }
            else if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_RIGHTVALUE_MULTILINE)
            {
                $statements[] = new Assignment($objectPath, new Scalar($tokens[$i + 1]->getValue()), $tokens[$i + 1]->getLine());
                $i += 1;
            }
        }
        else if ($tokens[$i]->getType() === TokenInterface::TYPE_CONDITION)
        {
            if ($context !== NULL)
            {
                throw new ParseError(
                    'Found condition statement inside nested assignment.',
                    1403011203,
                    $tokens[$i]->getLine()
                );
            }

            $count          = count($tokens);
            $ifStatements   = [];
            $elseStatements = [];

            $condition     = $tokens[$i]->getValue();
            $conditionLine = $tokens[$i]->getLine();
            $inElseBranch  = FALSE;

            for ($i++; $i < $count; $i++)
            {
                if ($tokens[$i]->getType() === TokenInterface::TYPE_CONDITION_END)
                {
                    $statements[] = new ConditionalStatement($condition, $ifStatements, $elseStatements, $conditionLine);
                    $i++;
                    break;
                }
                elseif ($tokens[$i]->getType() === TokenInterface::TYPE_CONDITION_ELSE)
                {
                    if ($inElseBranch)
                    {
                        throw new ParseError(
                            sprintf('Duplicate else in conditional statement in line %d.', $tokens[$i]->getLine()),
                            1403011203,
                            $tokens[$i]->getLine()
                        );
                    }
                    $inElseBranch = TRUE;
                    $i++;
                }

                if ($tokens[$i]->getType() === TokenInterface::TYPE_OBJECT_IDENTIFIER)
                {
                    $objectPath = new ObjectPath($tokens[$i]->getValue(), $tokens[$i]->getValue());
                    if ($tokens[$i + 1]->getType() === TokenInterface::TYPE_BRACE_OPEN)
                    {
                        $i += 2;
                        if ($inElseBranch)
                        {
                            $elseStatements[] = $this->parseNestedStatements($objectPath, $tokens, $i);
                        }
                        else
                        {
                            $ifStatements[] = $this->parseNestedStatements($objectPath, $tokens, $i);
                        }
                    }
                }

                if ($inElseBranch)
                {
                    $this->parseToken($tokens, $i, $elseStatements, NULL);
                }
                else
                {
                    $this->parseToken($tokens, $i, $ifStatements, NULL);
                }
            }
        }
        else if ($tokens[$i]->getType() === TokenInterface::TYPE_INCLUDE)
        {
            preg_match(Tokenizer::TOKEN_INCLUDE_STATEMENT, $tokens[$i]->getValue(), $matches);

            if ($matches['type'] === 'FILE')
            {
                $statements[] = new FileIncludeStatement($matches['filename'], $tokens[$i]->getLine());
            }
            else
            {
                $statements[] = new DirectoryIncludeStatement(
                    $matches['filename'], isset($matches['extension']) ? $matches['extension'] : NULL, $tokens[$i]->getLine()
                );
            }
        }
        else if ($tokens[$i]->getType() === TokenInterface::TYPE_WHITESPACE)
        {
            // Pass
        }
        else if ($tokens[$i]->getType() === TokenInterface::TYPE_BRACE_CLOSE)
        {
            if ($context === NULL)
            {
                throw new ParseError(
                    sprintf(
                        'Unexpected token %s when not in nested assignment in line %d.',
                        $tokens[$i]->getType(),
                        $tokens[$i]->getLine()
                    ),
                    1403011203,
                    $tokens[$i]->getLine()
                );
            }
        }
        else
        {
            throw new ParseError(
                sprintf('Unexpected token %s in line %d.', $tokens[$i]->getType(), $tokens[$i]->getLine()),
                1403011202,
                $tokens[$i]->getLine()
            );
        }
    }



    private function validateModifyOperatorRightValue(TokenInterface $token)
    {
        if ($token->getType() !== TokenInterface::TYPE_RIGHTVALUE)
        {
            throw new ParseError(
                'Unexpected token ' . $token->getType() . ' after modify operator.',
                1403010294,
                $token->getLine()
            );
        }

        if (!preg_match(Tokenizer::TOKEN_OBJECT_MODIFIER, $token->getValue()))
        {
            throw new ParseError(
                'Right side of modify operator does not look like a modifier: "' . $token->getValue() . '".',
                1403010700,
                $token->getLine()
            );
        }
    }



    private function validateCopyOperatorRightValue(TokenInterface $token)
    {
        if ($token->getType() !== TokenInterface::TYPE_RIGHTVALUE)
        {
            throw new ParseError(
                'Unexpected token ' . $token->getType() . ' after copy operator.',
                1403010294,
                $token->getLine()
            );
        }

        if (!preg_match(Tokenizer::TOKEN_OBJECT_REFERENCE, $token->getValue()))
        {
            throw new ParseError(
                'Right side of copy operator does not look like an object path: "' . $token->getValue() . '".',
                1403010699,
                $token->getLine()
            );
        }
    }



    /**
     * @param \Helmich\TsParser\Tokenizer\TokenInterface[] $tokens
     * @return \Helmich\TsParser\Tokenizer\TokenInterface[]
     */
    private function filterTokenStream($tokens)
    {
        $filteredTokens = [];
        $ignoredTokens  = [
            TokenInterface::TYPE_COMMENT_MULTILINE,
            TokenInterface::TYPE_COMMENT_ONELINE
        ];

        $maxLine = 0;

        foreach ($tokens as $token)
        {
            $maxLine = max($token->getLine(), $maxLine);

            // Trim unnecessary whitespace, but leave line breaks! These are important!
            if ($token->getType() === TokenInterface::TYPE_WHITESPACE)
            {
                $value = trim($token->getValue(), "\t ");
                if (strlen($value) > 0)
                {
                    $filteredTokens[] = new Token(
                        TokenInterface::TYPE_WHITESPACE,
                        $value,
                        $token->getLine()
                    );
                }
            }
            elseif (!in_array($token->getType(), $ignoredTokens))
            {
                $filteredTokens[] = $token;
            }
        }

        // Add two linebreak tokens; during parsing, we usually do not look more than two
        // tokens ahead; this hack ensures that there will always be at least two more tokens
        // present and we do not have to check whether these tokens exists.
        $filteredTokens[] = new Token(TokenInterface::TYPE_WHITESPACE, "\n", $maxLine + 1);
        $filteredTokens[] = new Token(TokenInterface::TYPE_WHITESPACE, "\n", $maxLine + 2);

        return $filteredTokens;
    }

}