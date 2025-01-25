<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Entity;

use EMS\CommonBundle\Entity\AssetStorage;
use PHPUnit\Framework\TestCase;

final class AssetStorageAiTest extends TestCase
{
    public function testAssetStorageEntity(): void
    {
        $asset = new AssetStorage();

        $asset->setHash('test_hash');
        $asset->setContents('test_contents');
        $asset->setSize(123);
        $asset->setConfirmed(true);

        $this->assertEquals('test_hash', $asset->getHash());
        $this->assertEquals('test_contents', $asset->getContents());
        $this->assertEquals(123, $asset->getSize());
        $this->assertTrue($asset->isConfirmed());
    }

    public function testUpdateModified(): void
    {
        $asset = new AssetStorage();

        $asset->updateModified();

        $this->assertInstanceOf(\DateTimeImmutable::class, $asset->getModified());
        $this->assertInstanceOf(\DateTimeImmutable::class, $asset->getCreated());
    }

    public function testUnexpectedNullValues(): void
    {
        $asset = new AssetStorage();

        $this->expectException(\RuntimeException::class);
        $asset->getHash();

        $this->expectException(\RuntimeException::class);
        $asset->getModified();

        $this->expectException(\RuntimeException::class);
        $asset->getCreated();

        $this->expectException(\RuntimeException::class);
        $asset->getSize();

        $this->expectException(\RuntimeException::class);
        $asset->isConfirmed();
    }
}
