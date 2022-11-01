<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Report;

use Helmich\TypoScriptParser\Parser\ParseError;
use Helmich\TypoScriptParser\Tokenizer\TokenizerException;

/**
 * A single checkstyle warning.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\Report
 */
class Issue
{

    const SEVERITY_INFO = "info";
    const SEVERITY_WARNING = "warning";
    const SEVERITY_ERROR = "error";

    /** @var int|null */
    private $line;

    /** @var int|null */
    private $column;

    /** @var string */
    private $message;

    /** @var string */
    private $severity;

    /** @var string */
    private $source;

    /**
     * Creates a new warning from a parse error.
     *
     * @param ParseError $parseError The parse error to convert into a warning.
     *
     * @return Issue The converted warning.
     */
    public static function createFromParseError(ParseError $parseError): self
    {
        return new self(
            $parseError->getSourceLine(),
            0,
            "Parse error: " . $parseError->getMessage(),
            self::SEVERITY_ERROR,
            get_class($parseError)
        );
    }

    /**
     * Creates a new warning from a tokenizer error.
     *
     * @param TokenizerException $tokenizerException The tokenizer error to convert into a warning.
     *
     * @return Issue The converted warning.
     */
    public static function createFromTokenizerError(TokenizerException $tokenizerException): self
    {
        return new self(
            $tokenizerException->getSourceLine(),
            0,
            "Tokenization error: " . $tokenizerException->getMessage(),
            self::SEVERITY_ERROR,
            get_class($tokenizerException)
        );
    }

    /**
     * Constructs a new warning.
     *
     * @param int|null $line The original source line the warning belongs to.
     * @param int|null $column The source column.
     * @param string $message The warning message.
     * @param string $severity The warning severity (see Issue::SEVERITY_* constants).
     * @param string $source An arbitrary identifier for the generator of this warning.
     */
    public function __construct(?int $line, ?int $column, string $message, string $severity, string $source)
    {
        $this->line = $line;
        $this->column = $column;
        $this->message = $message;
        $this->severity = $severity;
        $this->source = $source;
    }

    /**
     * Gets the original source line.
     *
     * @return int|null The original source line.
     */
    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * Gets the original source column, if applicable (else NULL).
     *
     * @return int|null The original source column, or NULL.
     */
    public function getColumn(): ?int
    {
        return $this->column;
    }

    /**
     * Gets the warning message.
     *
     * @return string The warning message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Gets the warning severity.
     *
     * @return string The warning severity (should be one of the Issue::SEVERITY_* constants).
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Gets the warning source identifier.
     *
     * @return string The warning source identifier.
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
