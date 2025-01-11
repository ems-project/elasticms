<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Composer;

use EMS\CommonBundle\Common\Composer\ComposerInfo;
use PHPUnit\Framework\TestCase;

final class ComposerInfoAiTest extends TestCase
{
    private ComposerInfo $composerInfo;

    #[\Override]
    protected function setUp(): void
    {
        $projectDir = __DIR__.'/fixtures';
        $this->composerInfo = new ComposerInfo($projectDir);
    }

    public function testBuild(): void
    {
        $this->composerInfo->build();

        $expectedPackages = [
            'core' => '1.0.0',
            'client' => '1.1.0',
            'common' => '1.2.0',
            'form' => '1.3.0',
            'submission' => '1.4.0',
            'symfony' => '5.3.0',
        ];

        $this->assertEquals($expectedPackages, $this->composerInfo->getVersionPackages());
    }

    public function testGetVersionPackagesWithoutBuild(): void
    {
        $this->assertEmpty($this->composerInfo->getVersionPackages());
    }
}
