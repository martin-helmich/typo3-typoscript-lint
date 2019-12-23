<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Util;

use SplFileInfo;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Finder\SplFileInfo as SymfonySplFileInfo; // @phan-suppress-current-line PhanUnreferencedUseNormal

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
     * @param string[]            $fileOrDirectoryNames A list of file and directory names.
     * @param string[]            $filePatterns         Glob patterns that filenames should match
     * @param FinderObserver|null $observer
     * @return string[] A list of file names.
     */
    public function getFilenames(array $fileOrDirectoryNames, array $filePatterns = [], FinderObserver $observer = null): array
    {
        $finder = clone $this->finder;
        $finder->files();

        $observer = $observer ?: new CallbackFinderObserver(function() {});

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
            $subFinder                   = clone $finder;
            $resolvedFileOrDirectoryName = $fileOrDirectoryName;

            if ($fileOrDirectoryName[0] !== '/' && substr($fileOrDirectoryName, 0, 6) !== 'vfs://') {
                $resolvedFileOrDirectoryName = realpath($fileOrDirectoryName);

                if ($resolvedFileOrDirectoryName === false) {
                    $observer->onEntryNotFound($fileOrDirectoryName);
                    continue;
                }
            }

            if (file_exists($resolvedFileOrDirectoryName) === false) {
                $observer->onEntryNotFound($resolvedFileOrDirectoryName);
                continue;
            }

            $fileInfo = $this->filesystem->openFile($resolvedFileOrDirectoryName);
            if ($fileInfo->isFile()) {
                $filenames[] = $resolvedFileOrDirectoryName;
            } else {
                $subFinder->in($resolvedFileOrDirectoryName);

                /** @var SymfonySplFileInfo $subFileInfo */
                foreach ($subFinder as $subFileInfo) {
                    $filenames[] = $subFileInfo->getPathname();
                }
            }
        }

        return $filenames;
    }
}
