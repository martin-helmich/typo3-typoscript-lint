<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\equalTo;
use function PHPUnit\Framework\identicalTo;
use function PHPUnit\Framework\any;

#[CoversClass(File::class)]
class FileTest extends TestCase
{

    private File $file;

    public function setUp(): void
    {
        $this->file = new File('test.tys');
    }

    public function testConstructorSetsFilename(): void
    {
        $this->assertEquals('test.tys', $this->file->getFilename());
    }

    public function testWarningsCanBeAdded(): void
    {
        $warning = new Issue(1, 1, "some warning", Issue::SEVERITY_WARNING, self::class);
        $this->file->addIssue($warning);

        assertCount(1, $this->file->getIssues());
        assertSame($warning, $this->file->getIssues()[0]);
    }

    /**
     * @depends testWarningsCanBeAdded
     */
    public function testWarningsAreSortedByLineNumber(): void
    {
        $warning1 = new Issue(10, 1, "some warning", Issue::SEVERITY_WARNING, self::class);
        $warning2 = new Issue(1, 1, "some warning", Issue::SEVERITY_WARNING, self::class);

        $this->file->addIssue($warning1);
        $this->file->addIssue($warning2);

        assertSame($warning2, $this->file->getIssues()[0]);
        assertSame($warning1, $this->file->getIssues()[1]);
    }

    public function testIssuesCanBeFilteredBySeverity(): void
    {
        $notice = new Issue(1, 1, "some notice", Issue::SEVERITY_INFO, self::class);
        $warning = new Issue(1, 1, "some warning", Issue::SEVERITY_WARNING, self::class);

        $this->file->addIssue($notice);
        $this->file->addIssue($warning);

        $warnings = $this->file->getIssuesBySeverity(Issue::SEVERITY_WARNING);

        assertCount(1, $warnings);
        assertSame($warnings[0], $warning);
    }
}
