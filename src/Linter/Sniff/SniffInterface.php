<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

interface SniffInterface
{
    /**
     * @param array<mixed, mixed> $parameters
     */
    public function __construct(array $parameters);
}
