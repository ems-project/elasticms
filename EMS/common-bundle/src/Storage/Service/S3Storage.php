<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Aws\S3\S3Client;
use EMS\CommonBundle\Common\Cache\Cache;
use EMS\Helpers\Standard\Json;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class S3Storage extends AbstractUrlStorage
{
    private ?S3Client $s3Client = null;
    private ?string $bucketHash = null;

    /**
     * @param array{version?: string, credentials?: array{key: string, secret: string}, region?: string} $credentials
     */
    public function __construct(LoggerInterface $logger, private readonly Cache $cache, private readonly array $credentials, private readonly string $bucket, int $usage, int $hotSynchronizeLimit = 0, private readonly ?string $uploadFolder = null, private readonly bool $multipartUpload = false)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
    }

    protected function getBaseUrl(): string
    {
        $this->getS3Client();

        return "s3://$this->bucket";
    }

    public function __toString(): string
    {
        return S3Storage::class." ($this->bucket)";
    }

    protected function getUploadPath(string $hash, string $ds = '/'): string
    {
        if (null === $this->uploadFolder) {
            return parent::getUploadPath($hash, $ds);
        }

        return \join($ds, [
            $this->uploadFolder,
            $this->getBucketHash(),
            $hash,
        ]);
    }

    public function addChunk(string $hash, string $chunk): bool
    {
        $s3 = $this->getS3Client();

        if ($this->multipartUpload) {
            $cache = $this->cache->getItem($this->uploadKey($hash));
            $args = $cache->get();
            $multipartUpload = $s3->uploadPart(\array_merge($args, [
                'Content-Length' => \strlen($chunk),
                'Body' => $chunk,
            ]));
            $eTag = $multipartUpload->get('ETag');
            $args['MultipartUpload']['Parts'][] = [
                'PartNumber' => $args['PartNumber']++,
                'ETag' => $eTag,
            ];
            $cache->set($args);
            $this->cache->save($cache);

            return \is_string($eTag);
        }

        if (null !== $this->uploadFolder) {
            return parent::addChunk($hash, $chunk);
        }

        $uploadKey = $this->uploadKey($hash);

        $head = $s3->headObject([
            'Bucket' => $this->bucket,
            'Key' => $uploadKey,
        ]);
        if (0 === $head['ContentLength']) {
            $upload = $s3->upload($this->bucket, $uploadKey, $chunk);

            return \is_string($upload['ETag'] ?? null);
        }

        $multipartUpload = $s3->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $uploadKey,
        ]);

        $uploadId = $multipartUpload['UploadId'];
        $uploadPartCopy = $s3->uploadPartCopy([
            'Bucket' => $this->bucket,
            'Key' => $uploadKey,
            'PartNumber' => 1,
            'UploadId' => $uploadId,
            'CopySource' => "$this->bucket/$uploadKey",
        ]);
        $parts[] = [
            'PartNumber' => 1,
            'ETag' => $uploadPartCopy['CopyPartResult']['ETag'],
        ];

        $uploadPart = $s3->uploadPart([
            'Bucket' => $this->bucket,
            'Key' => $uploadKey,
            'PartNumber' => 2,
            'UploadId' => $uploadId,
            'Content-Length' => \strlen($chunk),
            'Body' => $chunk,
        ]);
        $parts[] = [
            'PartNumber' => 2,
            'ETag' => $uploadPart['ETag'],
        ];

        $completeMultipartUpload = $s3->completeMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $uploadKey,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ]);

        return \is_string($completeMultipartUpload['ETag'] ?? null);
    }

    public function initUpload(string $hash, int $size, string $name, string $type): bool
    {
        $s3 = $this->getS3Client();
        if ($this->multipartUpload) {
            $uploadKey = $this->uploadKey($hash);
            $multipartUpload = $s3->createMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $uploadKey,
            ]);
            $uploadId = $multipartUpload->get('UploadId');
            $cache = $this->cache->getItem($uploadKey);
            $cache->set([
                'Bucket' => $this->bucket,
                'Key' => $uploadKey,
                'UploadId' => $uploadId,
                'PartNumber' => 1,
            ]);
            $this->cache->save($cache);

            return \is_string($uploadId);
        }

        if (null !== $this->uploadFolder) {
            return parent::initUpload($hash, $size, $name, $type);
        }

        $uploadKey = $this->uploadKey($hash);
        $result = $s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => $uploadKey,
            'Metadata' => [
                'Confirmed' => 'false',
            ],
        ]);

        return $result->hasKey('ETag');
    }

    public function finalizeUpload(string $hash): bool
    {
        $uploadKey = $this->uploadKey($hash);
        $key = $this->key($hash);
        $s3 = $this->getS3Client();
        $copy = $s3->copy($this->bucket, $uploadKey, $this->bucket, $key);
        $result = \is_string($copy['CopyObjectResult']['ETag'] ?? null);
        $this->removeUpload($hash);

        return $result;
    }

    public function read(string $hash, bool $confirmed = true): StreamInterface
    {
        if (!$confirmed && $this->multipartUpload) {
            $uploadKey = $this->uploadKey($hash);
            $cache = $this->cache->getItem($uploadKey);
            if ($cache->isHit()) {
                $args = $cache->get();
                $this->getS3Client()->completeMultipartUpload($args);
                $this->cache->delete($uploadKey);
            }
        }

        return parent::read($hash, $confirmed);
    }

    private function getBucketHash(): string
    {
        if (null !== $this->bucketHash) {
            return $this->bucketHash;
        }
        $this->bucketHash = \sha1(\sprintf('s3_%s_%s', $this->bucket, Json::encode($this->credentials)));

        return $this->bucketHash;
    }

    public function removeUpload(string $hash): void
    {
        if (null !== $this->uploadFolder) {
            parent::removeUpload($hash);

            return;
        }
        $this->getS3Client()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->uploadKey($hash),
        ]);
    }

    /**
     * @return resource
     */
    protected function getContext()
    {
        return \stream_context_create([
            's3' => ['seekable' => true],
        ]);
    }

    private function getS3Client(): S3Client
    {
        if (null === $this->s3Client) {
            $this->s3Client = new S3Client($this->credentials);
            $this->s3Client->registerStreamWrapper();
        }

        return $this->s3Client;
    }

    private function uploadKey(string $hash): string
    {
        return "uploads/$hash";
    }

    private function key(string $hash): string
    {
        $folder = \substr($hash, 0, 3);

        return "$folder/$hash";
    }
}
