<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Util;
use Helmich\TypoScriptLint\Util\Filesystem;
use Helmich\TypoScriptLint\Util\Finder;
use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * @covers \Helmich\TypoScriptLint\Util\Finder
 */
class FinderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @requires PHP 5.6
     */
    public function testFilenameListIsGenerated()
    {
        $sfFinder   = $this->getMockBuilder(SymfonyFinder::class)->disableOriginalConstructor()->getMock();
        $filesystem = $this->getMockBuilder(Filesystem::class)->disableOriginalConstructor()->getMock();

        $fib = $this->getMockBuilder('SplFileInfo')->disableOriginalConstructor();
        $fi1 = $fib->getMock();
        $fi1->expects($this->any())->method('isFile')->willReturn(false);
        $fi2 = $fib->getMock();
        $fi2->expects($this->any())->method('isFile')->willReturn(true);
        $fi3 = $fib->getMock();
        $fi3->expects($this->any())->method('isFile')->willReturn(true);

        $fi4 = $fib->getMock();
        $fi4->expects($this->any())->method('getPathname')->willReturn('directory/file3');
        $fi5 = $fib->getMock();
        $fi5->expects($this->any())->method('getPathname')->willReturn('directory/file4');

        $sfFinder->expects($this->once())->method('files')->willReturnSelf();
        $sfFinder->expects($this->once())->method('in')->with('directory');
        $sfFinder->expects($this->once())->method('getIterator')->willReturn(new \ArrayObject([$fi4, $fi5]));

        $finder = new Finder($sfFinder, $filesystem);

        $filesystem->expects($this->at(0))->method('openFile')->with('directory')->willReturn($fi1);
        $filesystem->expects($this->at(1))->method('openFile')->with('file1')->willReturn($fi2);
        $filesystem->expects($this->at(2))->method('openFile')->with('file2')->willReturn($fi3);

        $filenames = $finder->getFilenames(['directory', 'file1', 'file2']);

        $this->assertEquals(['directory/file3', 'directory/file4', 'file1', 'file2'], $filenames);
    }
}
