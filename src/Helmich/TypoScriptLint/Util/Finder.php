<?php
namespace Helmich\TypoScriptLint\Util;


use Symfony\Component\Finder\Finder as SymfonyFinder;


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



    /** @var \Symfony\Component\Finder\Finder */
    private $finder;


    /** @var \Helmich\TypoScriptLint\Util\Filesystem */
    private $filesystem;



    /**
     * Constructs a new finder instance.
     *
     * @param \Symfony\Component\Finder\Finder        $finder     A finder.
     * @param \Helmich\TypoScriptLint\Util\Filesystem $filesystem A filesystem interface.
     */
    public function __construct(SymfonyFinder $finder, Filesystem $filesystem)
    {
        $this->finder     = $finder;
        $this->filesystem = $filesystem;
    }



    /**
     * Generates a list of file names from a list of file and directory names.
     *
     * @param array $fileOrDirectoryNames A list of file and directory names.
     * @return array A list of file names.
     */
    public function getFilenames(array $fileOrDirectoryNames)
    {
        $filenames = [];

        foreach ($fileOrDirectoryNames as $fileOrDirectoryName)
        {
            $fileInfo = $this->filesystem->getFileInfo($fileOrDirectoryName);
            if ($fileInfo->isFile())
            {
                $filenames[] = $fileOrDirectoryName;
            }
            else
            {
                $this->finder->files()->in($fileOrDirectoryName);

                /** @var \Symfony\Component\Finder\SplFileInfo $subFileInfo */
                foreach ($this->finder as $subFileInfo)
                {
                    $filenames[] = $subFileInfo->getPathname();
                }
            }
        }

        return $filenames;
    }

}