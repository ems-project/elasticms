<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\FileInterface;
use EMS\CommonBundle\Storage\Service\HttpStorage;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\File as FileHelper;
use Psr\Http\Message\StreamInterface;

final class File implements FileInterface
{
    public function __construct(private readonly Client $client, private readonly StorageManager $storageManager)
    {
    }

    public function uploadStream(StreamInterface $stream, string $filename, string $mimeType): string
    {
        $hash = $this->hashStream($stream);
        if ($this->headHash($hash)) {
            return $hash;
        }
        $size = $stream->getSize();
        if (null === $size) {
            throw new \RuntimeException('Unexpected null size');
        }
        $fromByte = $this->initUpload($hash, $size, $filename, $mimeType);
        if ($fromByte < 0) {
            throw new \RuntimeException(\sprintf('Unexpected negative offset: %d', $fromByte));
        }
        if ($fromByte > $size) {
            throw new \RuntimeException(\sprintf('Unexpected bigger offset than the filesize: %d > %d', $fromByte, $size));
        }
        $stream->seek($fromByte);

        $uploaded = $fromByte;
        while (!$stream->eof()) {
            $uploaded = $this->addChunk($hash, $stream->read(819200));
        }

        if ($uploaded !== $size) {
            throw new \RuntimeException(\sprintf('Sizes mismatched %d vs. %d for assets %s', $uploaded, $size, $hash));
        }

        return $hash;
    }

    public function uploadFile(string $realPath, ?string $mimeType = null, ?string $filename = null, ?callable $callback = null): string
    {
        $hash = $this->hashFile($realPath);

        $file = FileHelper::fromFilename($realPath);
        $mimeType ??= $file->mimeType;
        $filename ??= $file->name;

        $fromByte = $this->initUpload($hash, $file->size, $filename, $mimeType);

        if ($fromByte < 0) {
            throw new \RuntimeException(\sprintf('Unexpected negative offset: %d', $fromByte));
        }
        if ($fromByte > $file->size) {
            throw new \RuntimeException(\sprintf('Unexpected bigger offset than the filesize: %d > %d', $fromByte, $file->size));
        }
        if ($fromByte === $file->size) {
            return $hash;
        }

        $uploaded = $fromByte;

        foreach ($file->chunk($fromByte) as $chunk) {
            $uploaded = $this->addChunk($hash, $chunk);
            if (null !== $callback) {
                $callback($chunk);
            }
        }

        if ($uploaded !== $file->size) {
            throw new \RuntimeException(\sprintf('Sizes mismatched %d vs. %d for assets %s', $uploaded, $file->size, $hash));
        }

        return $hash;
    }

    public function hashFile(string $filename): string
    {
        return $this->storageManager->computeFileHash($filename);
    }

    public function downloadLink(string $hash): string
    {
        return \sprintf('%s/data/file/%s', $this->client->getBaseUrl(), $hash);
    }

    public function hashStream(StreamInterface $stream): string
    {
        return $this->storageManager->computeStreamHash($stream);
    }

    public function initUpload(string $hash, int $size, string $filename, string $mimetype): int
    {
        $response = $this->client->post(HttpStorage::INIT_URL, HttpStorage::initBody($hash, $size, $filename, $mimetype));

        $data = $response->getData();
        if (!$response->isSuccess() || !\is_int($data['uploaded'] ?? null)) {
            throw new \RuntimeException(\sprintf('Init upload failed due to %s', $data['error'][0] ?? 'unknown reason'));
        }

        return $data['uploaded'];
    }

    public function addChunk(string $hash, string $chunk): int
    {
        $response = $this->client->postBody(HttpStorage::addChunkUrl($hash), $chunk);

        $data = $response->getData();
        if (!$response->isSuccess() || !\is_int($data['uploaded'] ?? null)) {
            throw new \RuntimeException(\sprintf('Add chunk failed due to %s', $data['error'][0] ?? 'unknown reason'));
        }

        return $data['uploaded'];
    }

    public function headFile(string $realPath): bool
    {
        $hash = $this->hashFile($realPath);

        return $this->headHash($hash);
    }

    public function headHash(string $hash): bool
    {
        try {
            return $this->client->head('/api/file/'.$hash);
        } catch (\Throwable) {
            return false;
        }
    }
}
