<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

interface SniffInterface
{
    public function __construct(array $parameters);
}
