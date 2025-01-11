<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\File\File;
use EMS\CommonBundle\Common\CoreApi\Result;
use EMS\CommonBundle\Storage\StorageManager;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class FileAiTest extends TestCase
{
    private Client $client;
    private StorageManager $storageManager;
    private File $file;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->storageManager = $this->createMock(StorageManager::class);
        $this->file = new File($this->client, $this->storageManager);
    }

    public function testUploadStream(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getSize')->willReturn(10);
        $stream->method('eof')->willReturn(true);

        $this->storageManager->expects($this->once())
            ->method('computeStreamHash')
            ->willReturn('sample-hash');

        $this->client->expects($this->once())
            ->method('post')
            ->willReturn($this->createMockResult(['uploaded' => 10]));

        $this->client->expects($this->once())
            ->method('head')
            ->willReturn(false);

        $hash = $this->file->uploadStream($stream, 'filename.txt', 'text/plain');

        $this->assertEquals('sample-hash', $hash);
    }

    public function testDownloadLink(): void
    {
        $hash = 'sample-hash';
        $expectedLink = \sprintf('%s/data/file/%s', $this->client->getBaseUrl(), $hash);

        $result = $this->file->downloadLink($hash);

        $this->assertEquals($expectedLink, $result);
    }

    public function testHashFile(): void
    {
        $filename = 'sample.txt';
        $hash = 'sample-hash';

        $this->storageManager->expects($this->once())
            ->method('computeFileHash')
            ->with($filename)
            ->willReturn($hash);

        $result = $this->file->hashFile($filename);

        $this->assertEquals($hash, $result);
    }

    public function testHashStream(): void
    {
        $stream = new Stream(\fopen('php://temp', 'r+'));
        \fwrite($stream->detach(), 'sample-content');
        $hash = 'sample-hash';

        $this->storageManager->expects($this->once())
            ->method('computeStreamHash')
            ->with($stream)
            ->willReturn($hash);

        $result = $this->file->hashStream($stream);

        $this->assertEquals($hash, $result);
    }

    public function testHeadFile(): void
    {
        $filename = 'sample.txt';
        $hash = 'sample-hash';

        $this->storageManager->expects($this->once())
            ->method('computeFileHash')
            ->with($filename)
            ->willReturn($hash);

        $this->client->expects($this->once())
            ->method('head')
            ->with('/api/file/'.$hash)
            ->willReturn(true);

        $result = $this->file->headFile($filename);

        $this->assertTrue($result);
    }

    public function testHeadHash(): void
    {
        $hash = 'sample-hash';

        $this->client->expects($this->once())
            ->method('head')
            ->with('/api/file/'.$hash)
            ->willReturn(true);

        $result = $this->file->headHash($hash);

        $this->assertTrue($result);
    }

    public function testInitUpload(): void
    {
        $hash = 'sample-hash';
        $size = 12345;
        $filename = 'sample.txt';
        $mimeType = 'text/plain';

        $this->client->expects($this->once())
            ->method('post')
            ->willReturn($this->createMockResult(['uploaded' => 0]));

        $result = $this->file->initUpload($hash, $size, $filename, $mimeType);

        $this->assertEquals(0, $result);
    }

    public function testAddChunk(): void
    {
        $hash = 'sample-hash';
        $chunk = 'sample-chunk';

        $this->client->expects($this->once())
            ->method('postBody')
            ->willReturn($this->createMockResult(['uploaded' => \strlen($chunk)]));

        $result = $this->file->addChunk($hash, $chunk);

        $this->assertEquals(\strlen($chunk), $result);
    }

    private function createMockResult(array $data): Result
    {
        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($data);
        $result->method('isSuccess')->willReturn(true);

        return $result;
    }
}
