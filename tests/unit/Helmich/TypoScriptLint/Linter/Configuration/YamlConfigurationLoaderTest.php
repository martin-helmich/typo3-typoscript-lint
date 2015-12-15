<?php
namespace Helmich\TypoScriptLint\Linter\Configuration;

/**
 * @covers  Helmich\TypoScriptLint\Linter\Configuration\YamlConfigurationLoader
 */
class YamlConfigurationLoaderTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private
        $fileLocator,
        $yamlParser,
        $filesystem;

    /** @var \Helmich\TypoScriptLint\Linter\Configuration\YamlConfigurationLoader */
    private $loader;

    public function setUp()
    {
        $this->fileLocator = $this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')->getMock();
        $this->yamlParser  = $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor(
        )->getMock();
        $this->filesystem  = $this->getMockBuilder('Helmich\TypoScriptLint\Util\Filesystem')->getMock();

        /** @noinspection PhpParamsInspection */
        $this->loader = new YamlConfigurationLoader($this->fileLocator, $this->yamlParser, $this->filesystem);
    }

    public function testLoadLocatesReadsAndParsesFile()
    {
        $file = $this->getMockBuilder('Symfony\\Component\\Finder\\SplFileInfo')->setConstructorArgs(
            ['php://memory', '', '']
        )->getMock();
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