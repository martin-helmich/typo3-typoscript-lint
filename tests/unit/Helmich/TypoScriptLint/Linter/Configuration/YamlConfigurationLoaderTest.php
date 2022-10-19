<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Configuration;

use Helmich\TypoScriptLint\Linter\Configuration\YamlConfigurationLoader;
use Helmich\TypoScriptLint\Util\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Configuration\YamlConfigurationLoader
 */
class YamlConfigurationLoaderTest extends TestCase
{

    /** @var MockObject */
    private
        $fileLocator,
        $yamlParser,
        $filesystem;

    /** @var YamlConfigurationLoader */
    private $loader;

    public function setUp(): void
    {
        $this->fileLocator = $this->getMockBuilder(FileLocatorInterface::class)->getMock();
        $this->yamlParser = $this->getMockBuilder(Parser::class)->disableOriginalConstructor()->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)->getMock();

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

    public function testLoadReturnsEmptyWhenFileIsNotFound()
    {
        if (!class_exists(FileLocatorFileNotFoundException::class)) {
            $this->markTestSkipped('requires Symfony 3.0 or newer');
        }

        $this->fileLocator->expects($this->once())
            ->method('locate')
            ->with('foobar.yml')
            ->willThrowException(new FileLocatorFileNotFoundException());
        $this->assertEquals([], $this->loader->load('foobar.yml'));
    }

    public function testSupportReturnsTrueForYamlFilenamesWithYmlExtension()
    {
        $this->assertTrue($this->loader->supports('foobar.yml'));
    }

    public function testSupportReturnsTrueForYamlFilenamesWithYamlExtension()
    {
        $this->assertTrue($this->loader->supports('foobar.yaml'));
    }

    public function testSupportReturnsFalseForNoneYamlFilenames()
    {
        $this->assertFalse($this->loader->supports('foobar.xml'));
    }
}
