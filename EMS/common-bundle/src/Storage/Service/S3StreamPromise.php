<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Aws\S3\S3Client;
use EMS\Helpers\Standard\Type;
use Psr\Http\Message\StreamInterface;

class S3StreamPromise implements StreamInterface
{
    private ?int $size = null;
    private int $offset = 0;
    private string $contents;

    public function __construct(private readonly S3Client $s3Client, private readonly string $bucket, private readonly string $key)
    {
    }

    #[\Override]
    public function __toString(): string
    {
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
        ]);
        $stream = $result['Body'] ?? null;
        if (!$stream instanceof StreamInterface) {
            throw new \RuntimeException('Unexpected non StreamInterface from S3 bucket');
        }

        return $stream->getContents();
    }

    #[\Override]
    public function close(): void
    {
    }

    #[\Override]
    public function detach()
    {
        return null;
    }

    #[\Override]
    public function getSize(): int
    {
        if (null !== $this->size) {
            return $this->size;
        }
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
            'Range' => 'bytes=0-0',
        ]);
        $contentRangeExploded = \explode('/', Type::string($result['ContentRange'] ?? null));
        $this->size = \intval(\end($contentRangeExploded));

        return $this->size;
    }

    #[\Override]
    public function tell(): int
    {
        return $this->offset;
    }

    #[\Override]
    public function eof(): bool
    {
        return $this->offset >= $this->getSize();
    }

    #[\Override]
    public function isSeekable(): bool
    {
        return true;
    }

    #[\Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        switch ($whence) {
            case SEEK_SET:
                $this->offset = $offset;
                break;
            case SEEK_CUR:
                $this->offset += $offset;
                break;
            case SEEK_END:
                $this->offset = $this->getSize() + $offset;
                break;
            default:
                throw new \InvalidArgumentException('Invalid whence');
        }
    }

    #[\Override]
    public function rewind(): void
    {
        $this->offset = 0;
    }

    #[\Override]
    public function isWritable(): bool
    {
        return false;
    }

    #[\Override]
    public function write(string $string): never
    {
        throw new \RuntimeException('Write is not supported');
    }

    #[\Override]
    public function isReadable(): bool
    {
        return true;
    }

    #[\Override]
    public function read(int $length): string
    {
        $endOffset = \min($this->offset + $length - 1, $this->getSize() - 1);
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
            'Range' => \sprintf('bytes=%d-%d', $this->offset, $endOffset),
        ]);
        $this->offset += $length;
        if ($this->offset > $this->getSize()) {
            $this->offset = $this->getSize();
        }
        $stream = $result['Body'] ?? null;
        if (!$stream instanceof StreamInterface) {
            throw new \RuntimeException('Unexpected non StreamInterface from S3 bucket');
        }
        $this->contents = $stream->getContents();

        return $this->contents;
    }

    #[\Override]
    public function getContents(): string
    {
        return $this->read($this->getSize() - $this->offset);
    }

    #[\Override]
    public function getMetadata(?string $key = null): void
    {
    }
}
