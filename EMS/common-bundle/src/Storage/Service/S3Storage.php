<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Aws\S3\S3Client;
use EMS\CommonBundle\Common\Cache\Cache;
use EMS\Helpers\Standard\Base64;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class S3Storage extends AbstractUrlStorage
{
    private ?S3Client $s3Client = null;

    /**
     * @param array{version?: string, credentials?: array{key: string, secret: string}, region?: string} $credentials
     */
    public function __construct(LoggerInterface $logger, private readonly Cache $cache, private readonly array $credentials, private readonly string $bucket, int $usage, int $hotSynchronizeLimit = 0, private readonly bool $multipartUpload = false)
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

    public function addChunk(string $hash, string $chunk): bool
    {
        $s3 = $this->getS3Client();

        $base64Hash = Base64::encode(\sha1($chunk, true));
        if ($this->multipartUpload) {
            $cache = $this->cache->getItem($this->uploadKey($hash));
            $args = $cache->get();
            $multipartUpload = $s3->uploadPart(\array_merge($args, [
                'Content-Length' => \strlen($chunk),
                'Body' => $chunk,
                'ChecksumSHA1' => $base64Hash,
            ]));
            $eTag = $multipartUpload->get('ETag');
            $args['MultipartUpload']['Parts'][] = [
                'PartNumber' => $args['PartNumber']++,
                'ETag' => $eTag,
                'ChecksumSHA1' => $base64Hash,
            ];
            $cache->set($args);
            $this->cache->save($cache);

            return \is_string($eTag);
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
        $uploadKey = $this->uploadKey($hash);

        if ($this->multipartUpload) {
            $key = $this->key($hash);
            $multipartUpload = $s3->createMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'ChecksumAlgorithm' => 'SHA1',
            ]);
            $uploadId = $multipartUpload->get('UploadId');
            $cache = $this->cache->getItem($uploadKey);
            $cache->set([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
                'PartNumber' => 1,
                'ChecksumAlgorithm' => 'SHA1',
            ]);
            $this->cache->save($cache);

            return \is_string($uploadId);
        }

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
        if ($this->multipartUpload) {
            return true;
        }
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
            $confirmed = true;
        }

        return parent::read($hash, $confirmed);
    }

    public function initFinalize(string $hash): void
    {
        if (!$this->multipartUpload) {
            return;
        }

        $uploadKey = $this->uploadKey($hash);
        $cache = $this->cache->getItem($uploadKey);
        if (!$cache->isHit()) {
            throw new \RuntimeException('Missing multipart upload');
        }

        $this->getS3Client()->completeMultipartUpload($cache->get());
        $this->cache->delete($uploadKey);
    }

    public function removeUpload(string $hash): void
    {
        $this->getS3Client()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->multipartUpload ? $this->key($hash) : $this->uploadKey($hash),
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
        if ($this->multipartUpload) {
            return "upload_$hash";
        }

        return "upload/$hash";
    }

    private function key(string $hash): string
    {
        $folder = \substr($hash, 0, 3);

        return "$folder/$hash";
    }
}
