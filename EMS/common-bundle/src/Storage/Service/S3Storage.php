<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Storage\Service;

use Aws\S3\S3Client;
use EMS\CommonBundle\Common\Standard\Json;
use Psr\Log\LoggerInterface;

class S3Storage extends AbstractUrlStorage
{
    /** @var S3Client */
    private $s3Client = null;

    /** @var string */
    private $bucket;

    /** @var array{version?:string,credentials?:array{key:string,secret:string},region?:string} */
    private $credentials;
    private ?string $uploadFolder;
    private ?string $bucketHash = null;

    /**
     * @param array{version?:string,credentials?:array{key:string,secret:string},region?:string} $s3Credentials
     */
    public function __construct(LoggerInterface $logger, array $s3Credentials, string $s3Bucket, int $usage, int $hotSynchronizeLimit = 0, string $uploadFolder = null)
    {
        parent::__construct($logger, $usage, $hotSynchronizeLimit);
        $this->bucket = $s3Bucket;
        $this->credentials = $s3Credentials;
        $this->uploadFolder = $uploadFolder;
    }

    protected function getBaseUrl(): string
    {
        if (null === $this->s3Client) {
            $this->s3Client = new S3Client($this->credentials);
            $this->s3Client->registerStreamWrapper();
        }

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

    public function initUpload(string $hash, int $size, string $name, string $type): bool
    {
        if (null !== $this->uploadFolder) {
            return parent::initUpload($hash, $size, $name, $type);
        }

        $path = $this->getUploadPath($hash);
        $this->initDirectory($path);
        $result = $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => \substr($path, 1 + \strlen($this->getBaseUrl())),
            'Metadata' => [
                'Confirmed' => 'false',
            ],
        ]);

        return $result->hasKey('ETag');
    }

    public function finalizeUpload(string $hash): bool
    {
        $source = $this->getUploadPath($hash);
        $destination = $this->getPath($hash);
        $result = \copy($source, $destination);
        $this->removeUpload($hash);

        return $result;
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
        $source = $this->getUploadPath($hash);
        $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => \substr($source, 1 + \strlen($this->getBaseUrl())),
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
}
