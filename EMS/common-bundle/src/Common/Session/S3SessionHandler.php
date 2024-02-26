<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Session;

use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

class S3SessionHandler extends AbstractSessionHandler
{
    private ?S3Client $s3Client = null;
    private bool $gcCalled = false;

    /**
     * @param array<string, mixed> $credentials
     */
    public function __construct(private readonly array $credentials, private readonly string $bucket, private readonly ?int $ttl = null)
    {
    }

    protected function doRead(string $sessionId): string
    {
        if (!$this->getS3Client()->doesObjectExist($this->bucket, $sessionId)) {
            return '';
        }

        $file = $this->getS3Client()->getObject([
            'Bucket' => $this->bucket,
            'Key' => $sessionId,
        ]);
        $expires = $file->get('Expires');
        if (!$expires instanceof \DateTime || $expires < new \DateTime()) {
            return '';
        }

        return (string) $file->get('Body');
    }

    protected function doWrite(string $sessionId, string $data): bool
    {
        $ttl = (int) ($this->ttl ?? \ini_get('session.gc_maxlifetime'));
        $expires = new \DateTimeImmutable(\sprintf('%d seconds', $ttl));
        $this->getS3Client()->putObject([
            'Bucket' => $this->bucket,
            'Key' => $sessionId,
            'Body' => $data,
            'Expires' => $expires->getTimestamp(),
        ]);

        return true;
    }

    protected function doDestroy(string $sessionId): bool
    {
        $this->getS3Client()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $sessionId,
        ]);

        return true;
    }

    public function close(): bool
    {
        if (!$this->gcCalled) {
            return true;
        }

        $files = $this->getS3Client()->listObjects([
            'Bucket' => $this->bucket,
        ]);
        $contents = $files->get('Contents');
        $now = new \DateTime();
        foreach ($contents as $file) {
            $meta = $this->getS3Client()->headObject([
                'Bucket' => $this->bucket,
                'Key' => $file['Key'],
            ]);
            if ($meta->get('Expires') > $now) {
                continue;
            }
            $this->getS3Client()->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $file['Key'],
            ]);
        }
        $this->gcCalled = false;

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->gcCalled = true;

        return 0;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        $this->doWrite($id, $data);

        return true;
    }

    private function getS3Client(): S3Client
    {
        if (null === $this->s3Client) {
            $this->s3Client = new S3Client($this->credentials);
        }

        return $this->s3Client;
    }
}
