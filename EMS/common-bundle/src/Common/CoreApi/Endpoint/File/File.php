<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\FileInterface;
use EMS\CommonBundle\Storage\Service\HttpStorage;
use EMS\CommonBundle\Storage\StorageManager;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

final class File implements FileInterface
{
    private Client $client;
    private StorageManager $storageManager;

    public function __construct(Client $client, StorageManager $storageManager)
    {
        $this->client = $client;
        $this->storageManager = $storageManager;
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
        $filesize = \filesize($realPath);
        if (!\is_int($filesize)) {
            throw new \RuntimeException('Unexpected file size type');
        }

        $symfonyFile = new SymfonyFile($realPath, false);
        $exploded = \explode(DIRECTORY_SEPARATOR, $realPath);

        $mimeType ??= $symfonyFile->guessExtension() ?? 'application/octet-stream';
        $filename ??= \end($exploded);

        $fromByte = $this->initUpload($hash, $filesize, $filename, $mimeType);
        if ($fromByte < 0) {
            throw new \RuntimeException(\sprintf('Unexpected negative offset: %d', $fromByte));
        }
        if ($fromByte > $filesize) {
            throw new \RuntimeException(\sprintf('Unexpected bigger offset than the filesize: %d > %d', $fromByte, $filesize));
        }

        $handle = \fopen($realPath, 'r');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Unexpected error while open the archive %s', $realPath));
        }
        if ($fromByte > 0) {
            if (0 !== \fseek($handle, $fromByte)) {
                throw new \RuntimeException(\sprintf('Unexpected error while seeking the file pointer at position %s', $fromByte));
            }
        }

        if ($fromByte === $filesize) {
            return $hash;
        }

        $uploaded = $fromByte;
        while (!\feof($handle)) {
            $chunk = \fread($handle, 819200);
            if (!\is_string($chunk)) {
                throw new \RuntimeException('Unexpected chunk type');
            }
            $uploaded = $this->addChunk($hash, $chunk);

            if (null !== $callback) {
                $callback($chunk);
            }
        }
        \fclose($handle);
        if ($uploaded !== $filesize) {
            throw new \RuntimeException(\sprintf('Sizes mismatched %d vs. %d for assets %s', $uploaded, $filesize, $hash));
        }

        return $hash;
    }

    public function hashFile(string $filename): string
    {
        return $this->storageManager->computeFileHash($filename);
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
        } catch (\Throwable $e) {
            return false;
        }
    }
}
