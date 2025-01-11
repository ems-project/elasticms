<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\File\FileInterface;
use EMS\CommonBundle\Storage\Processor\Config;
use EMS\CommonBundle\Storage\StreamWrapper;
use EMS\Helpers\Html\MimeTypes;
use EMS\Helpers\Standard\Base64;
use GuzzleHttp\Promise\Each;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\SplFileInfo;

class S3Storage extends AbstractUrlStorage
{
    private ?S3Client $s3Client = null;
    private bool $streamWrapperRegistered = false;

    /**
     * @param array{version?: string, credentials?: array{key: string, secret: string}, region?: string} $credentials
     */
    public function __construct(LoggerInterface $logger, private readonly Cache $cache, private readonly array $credentials, private readonly string $bucket, int $usage, int $hotSynchronizeLimit = 0, private readonly bool $multipartUpload = false)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
    }

    #[\Override]
    protected function getBaseUrl(): string
    {
        if (!$this->streamWrapperRegistered) {
            $this->getS3Client()->registerStreamWrapper();
            $this->streamWrapperRegistered = true;
        }

        return "s3://$this->bucket";
    }

    #[\Override]
    public function __toString(): string
    {
        return S3Storage::class." ($this->bucket)";
    }

    #[\Override]
    public function addChunk(string $hash, string $chunk): bool
    {
        $s3 = $this->getS3Client();

        if ($this->multipartUpload) {
            $base64Hash = Base64::encode(\sha1($chunk, true));
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

    #[\Override]
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

    #[\Override]
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

    #[\Override]
    public function read(string $hash, bool $confirmed = true): StreamInterface
    {
        if (!$confirmed && $this->multipartUpload) {
            $confirmed = true;
        }

        return new S3StreamPromise($this->getS3Client(), $this->bucket, $confirmed ? $this->key($hash) : $this->uploadKey($hash));
    }

    #[\Override]
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

    #[\Override]
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
    #[\Override]
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
        }

        return $this->s3Client;
    }

    private function uploadKey(string $hash): string
    {
        if ($this->multipartUpload) {
            return \sprintf('uploads_%s_%s', $this->bucket, $hash);
        }

        return "uploads/$hash";
    }

    private function key(string $hash): string
    {
        $folder = \substr($hash, 0, 3);

        return "$folder/$hash";
    }

    #[\Override]
    public function readCache(Config $config): ?StreamInterface
    {
        try {
            $stream = $this->getS3Client()->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getCacheKey($config),
            ])['Body'] ?? null;
            if ($stream instanceof StreamInterface) {
                return $stream;
            }
        } catch (\RuntimeException) {
        }

        return null;
    }

    #[\Override]
    public function saveCache(Config $config, FileInterface $file): bool
    {
        try {
            $this->getS3Client()->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->getCacheKey($config),
                'SourceFile' => $file->getFilename(),
            ]);
        } catch (\RuntimeException) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function clearCache(): bool
    {
        $this->getS3Client()->deleteMatchingObjects($this->bucket, 'cache/');

        return true;
    }

    private function getCacheKey(Config $config): string
    {
        return \implode('/', [
            'cache',
            \substr($config->getAssetHash(), 0, 3),
            \substr($config->getAssetHash(), 3),
            \substr($config->getConfigHash(), 0, 3),
            \substr($config->getConfigHash(), 3),
        ]);
    }

    #[\Override]
    public function readFromArchiveInCache(string $hash, string $path): ?StreamWrapper
    {
        $cacheKey = \implode('/', [
            'cache',
            \substr($hash, 0, 3),
            \substr($hash, 3),
            $path,
        ]);
        try {
            $response = $this->getS3Client()->getObject([
                'Bucket' => $this->bucket,
                'Key' => $cacheKey,
            ]);
        } catch (\RuntimeException) {
            return null;
        }

        $hash = $response->get('Metadata')['hash'] ?? null;
        if (\is_string($hash)) {
            $masterResponse = $this->getS3Client()->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->key($hash),
            ]);
            $stream = $masterResponse['Body'] ?? null;
            $size = \intval($masterResponse['ContentLength']);
        } else {
            $stream = $response['Body'] ?? null;
            $size = \intval($response['ContentLength']);
        }

        if (!$stream instanceof StreamInterface) {
            return null;
        }

        return new StreamWrapper($stream, $response['ContentType'] ?? MimeTypes::APPLICATION_OCTET_STREAM->value, $size);
    }

    #[\Override]
    public function addFileInArchiveCache(string $hash, SplFileInfo $file, string $mimeType): bool
    {
        $result = $this->getS3Client()->putObject([
            'Bucket' => $this->bucket,
            'ContentType' => $mimeType,
            'Key' => \implode('/', [
                'cache',
                \substr($hash, 0, 3),
                \substr($hash, 3),
                $file->getRelativePathname(),
            ]),
            'SourceFile' => $file->getPathname(),
        ]);

        return $result->hasKey('ETag');
    }

    #[\Override]
    public function loadArchiveItemsInCache(string $archiveHash, Archive $archive, ?callable $callback = null): bool
    {
        $batch = [];
        $client = $this->getS3Client();
        foreach ($archive->iterator() as $item) {
            $batch[] = $client->getCommand('PutObject', [
                'Bucket' => $this->bucket,
                'ContentType' => $item->type,
                'Key' => \implode('/', [
                    'cache',
                    \substr($archiveHash, 0, 3),
                    \substr($archiveHash, 3),
                    $item->filename,
                ]),
                'MetadataDirective' => 'REPLACE',
                'Metadata' => [
                    'hash' => $item->hash,
                ],
            ]);
        }
        $pool = new CommandPool($client, $batch, [
            'concurrency' => 250,
            'fulfilled' => $callback,
            'rejected' => $callback,
        ]);
        $promise = $pool->promise();
        $promise->wait();

        return true;
    }

    #[\Override]
    public function heads(string ...$hashes): array
    {
        $client = $this->getS3Client();
        $notFound = [];

        $promiseGenerator = function () use ($hashes, $client, &$notFound) {
            foreach ($hashes as $hash) {
                yield $client->headObjectAsync([
                    'Bucket' => $this->bucket,
                    'Key' => \implode('/', [\substr($hash, 0, 3), $hash]),
                ])->then(onFulfilled: function () use (&$notFound) {
                    $notFound[] = true;
                }, onRejected: function (AwsException $exception) use (&$notFound, $hash) {
                    if ('NotFound' === $exception->getAwsErrorCode()) {
                        $notFound[] = $hash;
                    }
                });
            }
        };

        $promise = Each::ofLimit(iterable: $promiseGenerator(), concurrency: 500);
        $promise->wait();

        return $notFound;
    }
}
