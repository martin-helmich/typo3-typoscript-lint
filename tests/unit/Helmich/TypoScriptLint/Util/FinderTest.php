<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Util;

use Helmich\TypoScriptLint\Util\Filesystem;
use Helmich\TypoScriptLint\Util\Finder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use org\bovigo\vfs\vfsStream;

/**
 * @covers \Helmich\TypoScriptLint\Util\Finder
 */
class FinderTest extends TestCase
{
    public function setUp(): void
    {
        vfsStream::setup('root', null, [
            'file1.typoscript' => '',
            'file2.txt' => '',
            'directory' => [
                'file3.ts' => '',
                'file4' => ''
            ],
            'directory2' => [
                'file5.typoscript' => '',
            ]
        ]);
    }

    public function testFilenameListIsGenerated()
    {
        $sfFinder = new SymfonyFinder();
        $filesystem = new Filesystem();

        $finder = new Finder($sfFinder, $filesystem);
        $filenames = $finder->getFilenames(['vfs://root/directory', 'vfs://root/file1.typoscript', 'vfs://root/file2.txt']);

        assertThat($filenames, equalTo([
            'vfs://root/directory/file3.ts',
            'vfs://root/directory/file4',
            'vfs://root/file1.typoscript',
            'vfs://root/file2.txt'
        ]));
    }

    public function testDirectoriesAreNotSearchedTwice()
    {
        $sfFinder = new SymfonyFinder();
        $filesystem = new Filesystem();

        $finder = new Finder($sfFinder, $filesystem);
        $filenames = $finder->getFilenames(['vfs://root/directory', 'vfs://root/directory2']);

        assertThat($filenames, equalTo([
            'vfs://root/directory/file3.ts',
            'vfs://root/directory/file4',
            'vfs://root/directory2/file5.typoscript',
        ]));
    }

    public function testFilenamesAreFilteredByPatterns()
    {
        $sfFinder = new SymfonyFinder();
        $filesystem = new Filesystem();

        $finder = new Finder($sfFinder, $filesystem);
        $filenames = $finder->getFilenames(['vfs://root'], ['*.ts']);

        assertThat($filenames, self::equalTo(['vfs://root/directory/file3.ts']));
    }

    public function testNonExistingRelativeDirnameWillNotResultInInvalidArgumentException()
    {
        $sfFinder = new SymfonyFinder();
        $filesystem = new Filesystem();

        $finder = new Finder($sfFinder, $filesystem);
        $filenames = $finder->getFilenames(['./not/existing/dir']);

        assertThat($filenames, equalTo([]));
    }

}
