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
    const TOKEN_OBJECT_REFERENCE = ',^\.?([a-zA-Z0-9_\-]+(?:\.[a-zA-Z0-9_\-]+)*)$,';

    const TOKEN_NESTING_START = ',^\{$,';
    const TOKEN_NESTING_END = ',^\}$,';

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
    const TOKEN_INCLUDE_STATEMENT = ',^
        <INCLUDE_TYPOSCRIPT:\s+
        source="(?<type>FILE|DIR):(?<filename>[^"]+)"
        (?:\s+extension="(?<extension>[^"]+)")?
        \s*>
    $,x';



    /**
     * @param string $inputString
     * @throws \Helmich\TsParser\Tokenizer\TokenizerException
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
            if ($currentTokenType === TokenInterface::TYPE_COMMENT_MULTILINE)
            {
                if (preg_match(self::TOKEN_WHITESPACE, $line, $matches))
                {
                    $currentTokenValue .= $matches[0];
                    $line = substr($line, strlen($matches[0]));
                }

                if (preg_match(self::TOKEN_COMMENT_MULTILINE_END, $line, $matches))
                {
                    $currentTokenValue .= $matches[0];
                    $tokens[] = new Token(TokenInterface::TYPE_COMMENT_MULTILINE, $currentTokenValue, $currentLine);

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
                    $tokens[] = new Token(TokenInterface::TYPE_RIGHTVALUE_MULTILINE, $currentTokenValue . $matches[0], $currentLine);

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
                $tokens[] = new Token(TokenInterface::TYPE_WHITESPACE, $matches[0], $currentLine);
                $line     = substr($line, strlen($matches[0]));
            }

            if (preg_match(self::TOKEN_COMMENT_MULTILINE_BEGIN, $line, $matches))
            {
                $currentTokenValue = $line;
                $currentTokenType  = TokenInterface::TYPE_COMMENT_MULTILINE;
                continue;
            }

            $simpleTokens = [
                self::TOKEN_COMMENT_ONELINE => TokenInterface::TYPE_COMMENT_ONELINE,
                self::TOKEN_NESTING_END => TokenInterface::TYPE_BRACE_CLOSE,
                self::TOKEN_CONDITION => TokenInterface::TYPE_CONDITION,
                self::TOKEN_CONDITION_ELSE => TokenInterface::TYPE_CONDITION_ELSE,
                self::TOKEN_CONDITION_END => TokenInterface::TYPE_CONDITION_END,
                self::TOKEN_INCLUDE_STATEMENT => TokenInterface::TYPE_INCLUDE,
            ];

            foreach($simpleTokens as $pattern => $type)
            {
                if (preg_match($pattern, $line, $matches))
                {
                    $tokens[] = new Token($type, $matches[0], $currentLine);
                    continue 2;
                }
            }

            if (preg_match(self::TOKEN_OPERATOR_LINE, $line, $matches))
            {
                $tokens[] = new Token(TokenInterface::TYPE_OBJECT_IDENTIFIER, $matches[1], $currentLine);

                if ($matches[2])
                {
                    $tokens[] = new Token(TokenInterface::TYPE_WHITESPACE, $matches[2], $currentLine);
                }

                switch ($matches[3])
                {
                    case '=':
                    case ':=':
                    case '<':
                    case '<=':
                    case '>':
                        try
                        {
                            $tokens[] = new Token($this->getTokenTypeForBinaryOperator($matches[3]), $matches[3], $currentLine);
                        }
                        catch (UnknownOperatorException $exception)
                        {
                            throw new TokenizerException($exception->getMessage(), 1403084548, $exception, $currentLine);
                        }

                        if ($matches[4])
                        {
                            $tokens[] = new Token(TokenInterface::TYPE_WHITESPACE, $matches[4], $currentLine);
                        }

                        if (preg_match(self::TOKEN_OBJECT_NAME, $matches[5]))
                        {
                            $tokens[] = new Token(TokenInterface::TYPE_OBJECT_CONSTRUCTOR, $matches[5], $currentLine);
                        }
                        else if (strlen($matches[5]))
                        {
                            $tokens[] = new Token(TokenInterface::TYPE_RIGHTVALUE, $matches[5], $currentLine);
                        }

                        if ($matches[6])
                        {
                            $tokens[] = new Token(TokenInterface::TYPE_WHITESPACE, $matches[6], $currentLine);
                        }
                        break;
                    case '{':
                        $tokens[] = new Token(TokenInterface::TYPE_BRACE_OPEN, $matches[3], $currentLine);
                        break;
                    case '(':
                        $currentTokenValue = "(\n";
                        $currentTokenType  = TokenInterface::TYPE_RIGHTVALUE_MULTILINE;
                        break;
                    default:
                        throw new TokenizerException('Unknown operator: "' . $matches[3] . '"!', 1403084443, NULL, $currentLine);
                }

                continue;
            }

            if (strlen($line) === 0)
            {
                continue;
            }

            throw new TokenizerException('Cannot tokenize line "' . $line . '"', 1403084444, NULL, $currentLine);
        }

        if ($currentTokenType !== NULL)
        {
            throw new TokenizerException('Unterminated ' . $currentTokenType . '!', 1403084445, NULL, $currentLine);
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
     * @throws \Helmich\TsParser\Tokenizer\UnknownOperatorException
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
        throw new UnknownOperatorException('Unknown binary operator "' . $operator . '"!');
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