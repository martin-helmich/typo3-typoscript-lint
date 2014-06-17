<?php
namespace Helmich\TsParser\Tokenizer;


class Tokenizer implements TokenizerInterface
{



    const TOKEN_WHITESPACE = ',^[ \t\n]+,s';
    const TOKEN_COMMENT_ONELINE = ',^(#|/)[^\n]*,';
    const TOKEN_COMMENT_MULTILINE_BEGIN = ',^/\*,';
    const TOKEN_COMMENT_MULTILINE_END = ',^\*/,';
    const TOKEN_CONDITION = ',^(\[(browser|version|system|device|useragent|language|IP|hostname|applicationContext|hour|minute|month|year|dayofweek|dayofmonth|dayofyear|usergroup|loginUser|page\|[a-zA-Z0-9_]+|treeLevel|PIDinRootline|PIDupinRootline|compatVersion|globalVar|globalString|userFunc)\s*=\s(.*?)\](\|\||&&|$))+,';
    const TOKEN_CONDITION_ELSE = ',^\[else\],i';
    const TOKEN_CONDITION_END = ',^\[(global|end)\],i';

    const TOKEN_OBJECT_NAME = ',^(CASE|CLEARGIF|COA(?:_INT)?|COBJ_ARRAY|COLUMNS|CTABLE|EDITPANEL|FILES?|FLUIDTEMPLATE|FORM|HMENU|HRULER|IMAGE|IMG_RESOURCE|IMGTEXT|LOAD_REGISTER|MEDIA|MULTIMEDIA|OTABLE|QTOBJECT|RECORDS|RESTORE_REGISTER|SEARCHRESULT|SVG|SWFOBJECT|TEMPLATE|USER(?:_INT)?|GIFBUILDER|[GT]MENU(?:_LAYERS)?|(?:G|T|JS|IMG)MENUITEM)$,';
    const TOKEN_OBJECT_ACCESSOR = ',^([a-zA-Z0-9_\-]+(?:\.[a-zA-Z0-9_\-]+)*)$,';

    const TOKEN_OBJECT_MODIFIER = ',^
        (?<name>[a-zA-Z0-9]+)  # Modifier name
        (?:\s)*
        \(
        (?<arguments>[^\)]*)   # Argument list
        \)
    $,x';
    const TOKEN_OPERATOR_LINE = ',^
        ([a-zA-Z0-9_\-]+(?:\.[a-zA-Z0-9_\-]+)*)   # Left value (object accessor)
        (\s*)                                     # Whitespace
        (=|:=|<=|<|>|\{|\()                       # Operator
        (\s*)                                     # More whitespace
        (.*)                                      # Right value
        (\s*)                                     # Trailing whitespace
    $,x';



    /**
     * @param string $inputString
     * @throws TokenizerException
     * @return \Helmich\TsParser\Tokenizer\TokenInterface[]
     */
    public function tokenizeString($inputString)
    {
        $inputString = $this->preprocessContent($inputString);

        $tokens = [];

        $currentTokenType  = NULL;
        $currentTokenValue = '';

        $lines       = explode("\n", $inputString);
        $currentLine = 0;

        foreach ($lines as $line)
        {
            $currentLine++;
            if ($currentTokenType === Token::TYPE_COMMENT_MULTILINE)
            {
                if (preg_match(self::TOKEN_WHITESPACE, $line, $matches))
                {
                    $currentTokenValue .= $matches[0];
                    $line = substr($line, strlen($matches[0]));
                }

                if (preg_match(self::TOKEN_COMMENT_MULTILINE_END, $line, $matches))
                {
                    $currentTokenValue .= $matches[0];
                    $tokens[] = new Token(Token::TYPE_COMMENT_MULTILINE, $currentTokenValue, $currentLine);

                    $currentTokenValue = NULL;
                    $currentTokenType  = NULL;
                }
                else
                {
                    $currentTokenValue .= $line;
                }
                continue;
            }
            elseif ($currentTokenType === TokenInterface::TYPE_RIGHTVALUE_MULTILINE)
            {
                if (preg_match(',^\s*\),', $line, $matches))
                {
                    $tokens[] = new Token(Token::TYPE_RIGHTVALUE_MULTILINE, $currentTokenValue . $matches[0], $currentLine);

                    $currentTokenValue = NULL;
                    $currentTokenType  = NULL;
                }
                else
                {
                    $currentTokenValue .= $line . "\n";
                }
                continue;
            }

            if (count($tokens) !== 0)
            {
                $tokens[] = new Token(TokenInterface::TYPE_WHITESPACE, "\n", $currentLine - 1);
            }

            if (preg_match(self::TOKEN_WHITESPACE, $line, $matches))
            {
                $tokens[] = new Token(Token::TYPE_WHITESPACE, $matches[0], $currentLine);
                $line     = substr($line, strlen($matches[0]));
            }

            if (preg_match(self::TOKEN_COMMENT_MULTILINE_BEGIN, $line, $matches))
            {
                $currentTokenValue = $line;
                $currentTokenType  = Token::TYPE_COMMENT_MULTILINE;
                continue;
            }

            if (preg_match(self::TOKEN_COMMENT_ONELINE, $line, $matches))
            {
                $tokens[] = new Token(Token::TYPE_COMMENT_ONELINE, $matches[0], $currentLine);
                continue;
            }

            if (preg_match(',^\}$,', $line))
            {
                $tokens[] = new Token(TokenInterface::TYPE_BRACE_CLOSE, '}', $currentLine);
                continue;
            }

            if (preg_match(self::TOKEN_CONDITION, $line, $matches))
            {
                $tokens[] = new Token(TokenInterface::TYPE_CONDITION, $matches[0], $currentLine);
                continue;
            }
            else if (preg_match(self::TOKEN_CONDITION_ELSE, $line, $matches))
            {
                $tokens[] = new Token(TokenInterface::TYPE_CONDITION_ELSE, $matches[0], $currentLine);
                continue;
            }
            else if (preg_match(self::TOKEN_CONDITION_END, $line, $matches))
            {
                $tokens[] = new Token(TokenInterface::TYPE_CONDITION_END, $matches[0], $currentLine);
                continue;
            }

            if (preg_match(self::TOKEN_OPERATOR_LINE, $line, $matches))
            {
                $tokens[] = new Token(Token::TYPE_OBJECT_IDENTIFIER, $matches[1], $currentLine);

                if ($matches[2])
                {
                    $tokens[] = new Token(Token::TYPE_WHITESPACE, $matches[2], $currentLine);
                }

                switch ($matches[3])
                {
                    case '=':
                    case ':=':
                    case '<':
                    case '<=':
                    case '>':
                        $tokens[] = new Token($this->getTokenTypeForBinaryOperator($matches[3]), $matches[3], $currentLine);

                        if ($matches[4])
                        {
                            $tokens[] = new Token(Token::TYPE_WHITESPACE, $matches[4], $currentLine);
                        }

                        if (preg_match(self::TOKEN_OBJECT_NAME, $matches[5]))
                        {
                            $tokens[] = new Token(Token::TYPE_OBJECT_CONSTRUCTOR, $matches[5], $currentLine);
                        }
                        else if ($matches[5])
                        {
                            $tokens[] = new Token(Token::TYPE_RIGHTVALUE, $matches[5], $currentLine);
                        }

                        if ($matches[6])
                        {
                            $tokens[] = new Token(Token::TYPE_WHITESPACE, $matches[6], $currentLine);
                        }
                        break;
                    case '{':
                        $tokens[] = new Token(Token::TYPE_BRACE_OPEN, $matches[3], $currentLine);
                        break;
                    case '(':
                        $currentTokenValue = "(\n";
                        $currentTokenType  = Token::TYPE_RIGHTVALUE_MULTILINE;
                        break;
                    default:
                        throw new TokenizerException('Unknown operator: "' . $matches[3] . '"!');
                }

                continue;
            }

            if (strlen($line) === 0)
            {
                continue;
            }

            throw new TokenizerException('Cannot tokenize line "' . $line . '"');
        }

        if ($currentTokenType !== NULL)
        {
            throw new TokenizerException('Unterminated ' . $currentTokenType . '!');
        }

        return $tokens;
    }



    /**
     * @param string $inputStream
     * @return \Helmich\TsParser\Tokenizer\TokenInterface[]
     */
    public function tokenizeStream($inputStream)
    {
        $content = file_get_contents($inputStream);
        return $this->tokenizeString($content);
    }



    /**
     * @param string $operator
     * @return string
     * @throws TokenizerException
     */
    private function getTokenTypeForBinaryOperator($operator)
    {
        switch ($operator)
        {
            case '=':
                return TokenInterface::TYPE_OPERATOR_ASSIGNMENT;
            case '<':
                return TokenInterface::TYPE_OPERATOR_COPY;
            case '<=':
                return TokenInterface::TYPE_OPERATOR_REFERENCE;
            case ':=':
                return TokenInterface::TYPE_OPERATOR_MODIFY;
            case '>':
                return TokenInterface::TYPE_OPERATOR_DELETE;
        }
        throw new TokenizerException('Unknown binary operator "' . $operator . '"!');
    }



    private function preprocessContent($content)
    {
        // Replace CRLF with LF.
        $content = str_replace("\r\n", "\n", $content);

        // Remove trailing whitespaces.
        $lines   = explode("\n", $content);
        $lines   = array_map('rtrim', $lines);
        $content = implode("\n", $lines);

        return $content;
    }
}