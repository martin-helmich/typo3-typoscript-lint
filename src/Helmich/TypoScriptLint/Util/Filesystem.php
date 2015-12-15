<?php
namespace Helmich\TypoScriptLint\Util;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem extends SymfonyFilesystem
{

    /**
     * @param $filename
     * @return \Symfony\Component\Finder\SplFileInfo
     */
    public function openFile($filename)
    {
        $relative = $this->makePathRelative($filename, getcwd());
        return new SplFileInfo($filename, dirname($relative), $relative);
    }
}
