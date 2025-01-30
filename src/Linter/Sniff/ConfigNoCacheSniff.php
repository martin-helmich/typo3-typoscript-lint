<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\ConfigNoCacheVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\SniffVisitor;

class ConfigNoCacheSniff extends AbstractSyntaxTreeSniff
{
    private bool $allowNoCacheForPages = false;

    public function __construct(array $parameters)
    {
        if (isset($parameters["allowNoCacheForPages"])) {
            $this->allowNoCacheForPages = (bool)$parameters["allowNoCacheForPages"];
        }

        parent::__construct($parameters);
    }

    protected function buildVisitor(): SniffVisitor
    {
        return new ConfigNoCacheVisitor($this->allowNoCacheForPages);
    }
}
