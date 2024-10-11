<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Endpoint\File;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\File\FileInterface;
use EMS\CommonBundle\Storage\File\StorageFile;
use EMS\CommonBundle\Storage\Service\HttpStorage;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\File\File as FileHelper;
use Psr\Http\Message\StreamInterface;

final class File implements FileInterface
{
    private const HEADS_CHUNK_SIZE = 1000;

    public function __construct(private readonly Client $client, private readonly StorageManager $storageManager)
    {
    }

    public function uploadStream(StreamInterface $stream, string $filename, string $mimeType, bool $head = true): string
    {
        $hash = $this->hashStream($stream);
        if ($head && $this->headHash($hash)) {
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
            $uploaded = $this->addChunk($hash, $stream->read(FileHelper::DEFAULT_CHUNK_SIZE));
        }

        if ($uploaded !== $size) {
            throw new \RuntimeException(\sprintf('Sizes mismatched %d vs. %d for assets %s', $uploaded, $size, $hash));
        }

        return $hash;
    }

    public function uploadContents(string $contents, string $filename, string $mimeType): string
    {
        $hash = $this->storageManager->computeStringHash($contents);
        $size = \strlen($contents);
        $fromByte = $this->initUpload($hash, $size, $filename, $mimeType);
        $uploaded = $this->addChunk($hash, $fromByte > 0 ? \substr($contents, $fromByte) : $contents);
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

    public function downloadFile(string $hash): string
    {
        if (!$this->headHash($hash)) {
            throw new \RuntimeException(\sprintf('Could not download file with hash %s', $hash));
        }
        $stream = $this->client->download($this->downloadLink($hash));
        $storageFile = new StorageFile($stream);

        return $storageFile->getFilename();
    }

    public function downloadLink(string $hash): string
    {
        return \sprintf('%s/data/file/%s', $this->client->getBaseUrl(), $hash);
    }

    public function getHashAlgo(): string
    {
        return $this->client->get('/api/file/hash-algo')->getData()['hash_algo'];
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

    /**
     * @return iterable<string>
     */
    public function heads(string ...$fileHashes): iterable
    {
        $pagedHashes = \array_chunk($fileHashes, self::HEADS_CHUNK_SIZE, true);
        foreach ($pagedHashes as $hashes) {
            foreach ($this->client->post('/api/file/heads', $hashes)->getData() as $hash) {
                yield $hash;
            }
        }
    }
}
