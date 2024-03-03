<?php
declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Exception;
use Throwable;

class PrinterException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
