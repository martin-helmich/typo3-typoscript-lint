<?php
namespace Helmich\TypoScriptLint\Util;

interface FinderObserver
{
    public function onEntryNotFound($fileOrDirectory);
}