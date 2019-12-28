<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

class PathUtils
{
    public static function getRelativePath(string $absoluteFilename): string
    {
        if (substr($absoluteFilename, 0, mb_strlen(getcwd())) === getcwd()) {
            $relativeFilename = substr($absoluteFilename, mb_strlen(getcwd()));
            $relativeFilename = ltrim($relativeFilename, DIRECTORY_SEPARATOR);

            return $relativeFilename;
        }

        return $absoluteFilename;
    }
}
