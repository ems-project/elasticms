<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\StoreData\Service;

use Aws\S3\S3Client;
use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use EMS\Helpers\Standard\Json;

class StoreDataS3Service implements StoreDataServiceInterface
{
    private ?S3Client $s3Client = null;

    /**
     * @param array<string, mixed> $credentials
     */
    public function __construct(private readonly array $credentials, private readonly string $bucket, private readonly ?int $ttl = null)
    {
    }

    #[\Override]
    public function save(StoreDataHelper $data): void
    {
        $args = [
            'Bucket' => $this->bucket,
            'Key' => $data->getKey(),
            'Body' => Json::encode($data->getData(), true),
        ];
        if (null !== $this->ttl) {
            $expires = new \DateTimeImmutable(\sprintf('%d seconds', $this->ttl));
            $args['Expires'] = $expires->getTimestamp();
        }
        $this->getS3Client()->putObject($args);
    }

    #[\Override]
    public function read(string $key): ?StoreDataHelper
    {
        if (!$this->getS3Client()->doesObjectExistV2($this->bucket, $key)) {
            return null;
        }

        $file = $this->getS3Client()->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
        $expires = $file->get('Expires');
        if ($expires instanceof \DateTime && $expires < new \DateTime() && $expires->getTimestamp() > 0) {
            return null;
        }

        return new StoreDataHelper($key, Json::decode((string) $file->get('Body')));
    }

    #[\Override]
    public function delete(string $key): void
    {
        if (!$this->getS3Client()->doesObjectExistV2($this->bucket, $key)) {
            return;
        }
        $this->getS3Client()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }

    #[\Override]
    public function gc(): void
    {
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
            $expires = $meta->get('Expires');
            if (!$expires instanceof \DateTime) {
                continue;
            }
            if ($expires > $now || 0 === $expires->getTimestamp()) {
                continue;
            }
            $this->getS3Client()->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $file['Key'],
            ]);
        }
    }

    private function getS3Client(): S3Client
    {
        if (null === $this->s3Client) {
            $this->s3Client = new S3Client($this->credentials);
        }

        return $this->s3Client;
    }
}
