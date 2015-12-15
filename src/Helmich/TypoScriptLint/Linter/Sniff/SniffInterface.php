<?php
namespace Helmich\TypoScriptLint\Linter\Sniff;

interface SniffInterface
{

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters);
}