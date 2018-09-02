<?php
namespace Helmich\TypoScriptLint\Util;

use SplFileInfo;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo;

/**
 * Helper class that selects files to analyze from a list of file and directory names.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Util
 */
class Finder
{

    /** @var SymfonyFinder */
    private $finder;

    /** @var Filesystem */
    private $filesystem;

    /**
     * Constructs a new finder instance.
     *
     * @param SymfonyFinder $finder A finder.
     * @param Filesystem    $filesystem A filesystem interface.
     */
    public function __construct(SymfonyFinder $finder, Filesystem $filesystem)
    {
        $this->finder       = $finder;
        $this->filesystem   = $filesystem;
    }

    /**
     * Generates a list of file names from a list of file and directory names.
     *
     * @param array $fileOrDirectoryNames A list of file and directory names.
     * @param string[]      $filePatterns Glob patterns that filenames should match
     * @return array A list of file names.
     */
    public function getFilenames(array $fileOrDirectoryNames, array $filePatterns = [])
    {
        $finder = clone $this->finder;
        $finder->files();

        if (count($filePatterns) > 0) {
            $finder->filter(function(SplFileInfo $fileInfo) use ($filePatterns) {
                if ($fileInfo->isDir()) {
                    return true;
                }

                foreach ($filePatterns as $pattern) {
                    if (fnmatch($pattern, $fileInfo->getFilename())) {
                        return true;
                    }
                }
                return false;
            });
        }

        $filenames = [];

        foreach ($fileOrDirectoryNames as $fileOrDirectoryName) {
            $subFinder = clone $finder;

            if ($fileOrDirectoryName{0} !== '/' && substr($fileOrDirectoryName, 0, 6) !== 'vfs://') {
                $fileOrDirectoryName = realpath($fileOrDirectoryName);
            }

            $fileInfo = $this->filesystem->openFile($fileOrDirectoryName);
            if ($fileInfo->isFile()) {
                $filenames[] = $fileOrDirectoryName;
            } else {
                $subFinder->in($fileOrDirectoryName);

                /** @var SymfonySplFileInfo $subFileInfo */
                foreach ($subFinder as $subFileInfo) {
                    $filenames[] = $subFileInfo->getPathname();
                }
            }
        }

        return $filenames;
    }
}
