<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Configuration;
use Helmich\TypoScriptLint\Linter\Configuration\YamlConfigurationLoader;
use Helmich\TypoScriptLint\Util\Filesystem;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Configuration\YamlConfigurationLoader
 */
class YamlConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private
        $fileLocator,
        $yamlParser,
        $filesystem;

    /** @var YamlConfigurationLoader */
    private $loader;

    public function setUp()
    {
        $this->fileLocator = $this->getMockBuilder(FileLocatorInterface::class)->getMock();
        $this->yamlParser  = $this->getMockBuilder(Parser::class)->disableOriginalConstructor()->getMock();
        $this->filesystem  = $this->getMockBuilder(Filesystem::class)->getMock();

        /** @noinspection PhpParamsInspection */
        $this->loader = new YamlConfigurationLoader($this->fileLocator, $this->yamlParser, $this->filesystem);
    }

    public function testLoadLocatesReadsAndParsesFile()
    {
        $file = $this->getMockBuilder(SplFileInfo::class)->setConstructorArgs(['php://memory', '', ''])->getMock();
        $file->expects($this->once())->method('getContents')->willReturn('foo: bar');

        $this->fileLocator->expects($this->once())->method('locate')->with('foobar.yml')->willReturn('dir/foobar.yml');
        $this->filesystem->expects($this->once())->method('openFile')->with('dir/foobar.yml')->willReturn($file);
        $this->yamlParser->expects($this->once())->method('parse')->with('foo: bar')->willReturn(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->loader->load('foobar.yml'));
    }

    public function testSupportReturnsTrueForYamlFilenames()
    {
        $this->assertTrue($this->loader->supports('foobar.yml'));
    }

    public function testSupportReturnsFalseForNoneYamlFilenames()
    {
        $this->assertFalse($this->loader->supports('foobar.xml'));
    }
}
