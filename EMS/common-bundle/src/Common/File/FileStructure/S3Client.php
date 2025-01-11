<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File\FileStructure;

use Aws\CommandPool;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client as AwsS3Client;
use EMS\CommonBundle\Exception\FileStructureNotSyncException;
use EMS\CommonBundle\Helper\MimeTypeHelper;
use Psr\Http\Message\StreamInterface;

class S3Client implements FileStructureClientInterface
{
    private const string EMS_ARCHIVE_IDENTIFIER_FILE = '.ems_archive_digest';
    private readonly AwsS3Client $client;
    private string $hash;
    /** @var mixed[] */
    private array $batch;
    /** @var string[] */
    private array $existingFiles;

    /**
     * @param mixed[] $credential
     */
    public function __construct(array $credential, private readonly string $bucket)
    {
        $this->client = new AwsS3Client($credential);
    }

    #[\Override]
    public function initSync(string $hash): void
    {
        $this->hash = $hash;
        $this->batch = [];
        $this->existingFiles = [];

        $objects = $this->client->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
        ]);
        foreach ($objects as $object) {
            $this->existingFiles[$object['Key']] = $object['Key'];
        }
    }

    #[\Override]
    public function createFolder(string $path, string $getLabel): void
    {
    }

    #[\Override]
    public function createFile(string $path, StreamInterface $stream, string $contentType): void
    {
        $key = "$this->hash/$path";
        unset($this->existingFiles[$path]);
        $uploader = new MultipartUploader($this->client, $stream, [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
        $uploader->upload();
        $this->existingFiles[$key] = $key;
        $this->batch[] = $this->client->getCommand('CopyObject', [
            'Bucket' => $this->bucket,
            'Key' => $path,
            'CopySource' => \urlencode("$this->bucket/$key"),
            'MetadataDirective' => 'REPLACE',
            'ContentType' => $contentType,
        ]);
    }

    #[\Override]
    public function finalize(): void
    {
        $key = "$this->hash/".self::EMS_ARCHIVE_IDENTIFIER_FILE;
        $result = $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $this->hash,
            'ContentType' => MimeTypeHelper::TEXT_PLAIN,
        ]);
        $this->existingFiles[$key] = $key;
        $this->batch[] = $this->client->getCommand('CopyObject', [
            'Bucket' => $this->bucket,
            'Key' => self::EMS_ARCHIVE_IDENTIFIER_FILE,
            'CopySource' => \urlencode("$this->bucket/$key"),
        ]);
        unset($this->existingFiles[self::EMS_ARCHIVE_IDENTIFIER_FILE]);
        foreach ($this->existingFiles as $key => $object) {
            $this->batch[] = $this->client->getCommand('DeleteObject', [
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        }
        CommandPool::batch($this->client, $this->batch);
    }

    #[\Override]
    public function isUpToDate(): bool
    {
        try {
            $hash = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => self::EMS_ARCHIVE_IDENTIFIER_FILE,
            ])['Body']->__toString();
        } catch (\RuntimeException) {
            throw new FileStructureNotSyncException('It was not possible to get the current hash value');
        }

        return $this->hash === $hash;
    }
}
