<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

use Helmich\TypoScriptLint\Linter\Sniff\Visitor\NestingConsistencyVisitor;
use Helmich\TypoScriptLint\Linter\Sniff\Visitor\SniffVisitor;

/**
 * @phpstan-type NestingConsistencySniffParams array{
 *     commonPathPrefixThreshold: ?int
 * }
 */
class NestingConsistencySniff extends AbstractSyntaxTreeSniff
{
    private int $commonPathPrefixThreshold = 1;

    /**
     * @param NestingConsistencySniffParams $parameters
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        if (isset($parameters['commonPathPrefixThreshold']) && is_int($parameters['commonPathPrefixThreshold'])) {
            $this->commonPathPrefixThreshold = $parameters['commonPathPrefixThreshold'];
        }
    }

    protected function buildVisitor(): SniffVisitor
    {
        return new NestingConsistencyVisitor($this->commonPathPrefixThreshold);
    }
}
