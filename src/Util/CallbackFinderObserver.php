<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Util;

class CallbackFinderObserver implements FinderObserver
{
    /** @var callable */
    private $fn;

    public function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    public function onEntryNotFound(string $fileOrDirectory): void
    {
        // Because, fuck you, PHP
        $fn = $this->fn;
        $fn($fileOrDirectory);
    }

}
