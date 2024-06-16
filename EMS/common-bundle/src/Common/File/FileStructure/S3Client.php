<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File\FileStructure;

use Aws\CommandPool;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client as AwsS3Client;
use Psr\Http\Message\StreamInterface;

class S3Client implements FileStructureClientInterface
{
    private AwsS3Client $client;
    private string $hash;
    private string $identifier;
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

    public function initSync(string $identifier, string $hash): void
    {
        $this->hash = $hash;
        $this->identifier = $identifier;
        $this->batch = [];
        $this->existingFiles = [];

        $objects = $this->client->getIterator('ListObjects', [
            'Bucket' => $this->bucket,
        ]);
        foreach ($objects as $object) {
            $this->existingFiles[$object['Key']] = $object['Key'];
        }
    }

    public function createFolder(string $path, string $getLabel): void
    {
    }

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

    public function finalize(): void
    {
        $key = "$this->hash/$this->identifier";
        $result = $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $this->hash,
            'ContentType' => 'text/plain',
        ]);
        $this->existingFiles[$key] = $key;
        $this->batch[] = $this->client->getCommand('CopyObject', [
            'Bucket' => $this->bucket,
            'Key' => $this->identifier,
            'CopySource' => \urlencode("$this->bucket/$key"),
        ]);
        unset($this->existingFiles[$this->identifier]);
        foreach ($this->existingFiles as $key => $object) {
            $this->batch[] = $this->client->getCommand('DeleteObject', [
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        }
        CommandPool::batch($this->client, $this->batch);
    }

    public function isUpToDate(): bool
    {
        try {
            $hash = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->identifier,
            ])['Body']->__toString();
        } catch (\RuntimeException) {
            return false;
        }

        return $this->hash === $hash;
    }
}
