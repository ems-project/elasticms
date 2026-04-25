<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\File;

use EMS\CommonBundle\Common\File\FileInfo;
use PHPUnit\Framework\TestCase;

final class FileInfoTest extends TestCase
{
    public function testDeserialize(): void
    {
        $fileInfo = FileInfo::deserialize([
            'hash' => '1322c1902365afc55f783c692915d4365b29ceca',
            'name' => 'ems-hashcash.png',
            'type' => 'image/png',
            'file-object' => [
                'sha1' => '1322c1902365afc55f783c692915d4365b29ceca',
                '_hash' => '1322c1902365afc55f783c692915d4365b29ceca',
                'filesize' => 182318,
                '_size' => 182318,
                'filename' => 'ems-hashcash.png',
                '_name' => 'ems-hashcash.png',
                'mimetype' => 'image/png',
                '_type' => 'image/png',
                '_algo' => 'sha1',
            ],
            'first-seen' => [
                'date' => '2026-04-14 19:10:59.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC',
            ],
            'last-seen' => [
                'date' => '2026-04-14 19:10:59.000000',
                'timezone_type' => 3,
                'timezone' => 'UTC',
            ],
            'uploaded-by' => 'demo',
            'hidden' => false,
            'size' => 182318,
            'uploads' => 1,
            'head-counter' => 1,
        ]);

        $this->assertSame('1322c1902365afc55f783c692915d4365b29ceca', $fileInfo->getHash());
        $this->assertSame('ems-hashcash.png', $fileInfo->getName());
        $this->assertSame('image/png', $fileInfo->getType());
        $this->assertSame('demo', $fileInfo->getUploadedBy());
        $this->assertFalse($fileInfo->getHidden());
        $this->assertSame(182318, $fileInfo->getSize());
        $this->assertSame(1, $fileInfo->getUploads());
        $this->assertSame(1, $fileInfo->getHeadCounter());
        $this->assertSame('2026-04-14 19:10:59', $fileInfo->getFirstSeen()?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-04-14 19:10:59', $fileInfo->getLastUploaded()?->format('Y-m-d H:i:s'));
        $this->assertSame('ems-hashcash.png', $fileInfo->getFileObject()['filename'] ?? null);
    }
}
