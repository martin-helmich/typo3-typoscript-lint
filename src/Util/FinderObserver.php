<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Util;

interface FinderObserver
{
    public function onEntryNotFound($fileOrDirectory);
}