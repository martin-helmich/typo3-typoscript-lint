<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Util;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem extends SymfonyFilesystem
{

    /**
     * @param string $filename
     * @return SplFileInfo
     */
    public function openFile(string $filename): SplFileInfo
    {
        $relative = $this->makePathRelative($filename, getcwd());
        return new SplFileInfo($filename, dirname($relative), $relative);
    }
}
