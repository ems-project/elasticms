<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File;

use EMS\Helpers\Date\DateTimeConvertor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FileInfo implements \JsonSerializable
{
    /** @var array{sha1: string, _hash: string, filesize: int, _size: int, filename: string, _name: string, mimetype: string, _type: string, _algo: string}|null */
    #[SerializedName('file-object')]
    private ?array $fileObject = null;
    #[SerializedName('first-seen')]
    private ?\DateTimeImmutable $firstSeen = null;
    #[SerializedName('last-seen')]
    private ?\DateTimeImmutable $lastUploaded = null;
    private ?string $name = null;
    private ?string $type = null;
    #[SerializedName('uploaded-by')]
    private ?string $uploadedBy = null;
    private ?bool $hidden = null;
    private ?int $size = null;
    private ?int $uploads = null;
    #[SerializedName('head-counter')]
    private ?int $headCounter = null;

    public function __construct(private readonly string $hash)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function deserialize(array $data): self
    {
        if (!\is_string($data['hash'] ?? null)) {
            throw new \InvalidArgumentException('Missing or invalid "hash" for FileInfo deserialization');
        }

        $hash = $data['hash'];
        unset($data['hash']);
        $firstSeen = DateTimeConvertor::toDateTimeImmutable($data['first-seen'] ?? null);
        $lastSeen = DateTimeConvertor::toDateTimeImmutable($data['last-seen'] ?? null);
        unset($data['first-seen'], $data['last-seen']);

        $fileInfo = self::getSerializer()->denormalize($data, self::class, null, [
            AbstractNormalizer::DEFAULT_CONSTRUCTOR_ARGUMENTS => [
                self::class => ['hash' => $hash],
            ],
        ]);

        if (!$fileInfo instanceof self) {
            throw new \RuntimeException('Unexpected non FileInfo object');
        }

        $fileInfo->setFirstSeen($firstSeen);
        $fileInfo->setLastUploaded($lastSeen);

        return $fileInfo;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param array{sha1: string, _hash: string, filesize: int, _size: int, filename: string, _name: string, mimetype: string, _type: string, _algo: string} $fileObject
     */
    public function setFileObject(array $fileObject): void
    {
        $this->fileObject = $fileObject;
    }

    /**
     * @return array{sha1: string, _hash: string, filesize: int, _size: int, filename: string, _name: string, mimetype: string, _type: string, _algo: string}|null $fileObject
     */
    public function getFileObject(): ?array
    {
        return $this->fileObject;
    }

    /**
     * @return mixed[]
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'hash' => $this->hash,
            'name' => $this->name,
            'type' => $this->type,
            'file-object' => $this->fileObject,
            'first-seen' => $this->firstSeen,
            'last-seen' => $this->lastUploaded,
            'uploaded-by' => $this->uploadedBy,
            'hidden' => $this->hidden,
            'size' => $this->size,
            'uploads' => $this->uploads,
            'head-counter' => $this->headCounter,
        ];
    }

    private static function getSerializer(): DenormalizerInterface
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $reflectionExtractor = new ReflectionExtractor();
        $propertyTypeExtractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$reflectionExtractor]
        );

        return new Serializer([
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                $classMetadataFactory,
                new MetadataAwareNameConverter($classMetadataFactory),
                null,
                $propertyTypeExtractor
            ),
        ], [
            new JsonEncoder(),
        ]);
    }

    public function setFirstSeen(?\DateTimeImmutable $created): void
    {
        $this->firstSeen = $created;
    }

    public function setLastUploaded(?\DateTimeImmutable $modified): void
    {
        $this->lastUploaded = $modified;
    }

    public function getFirstSeen(): ?\DateTimeImmutable
    {
        return $this->firstSeen;
    }

    public function getLastUploaded(): ?\DateTimeImmutable
    {
        return $this->lastUploaded;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUploadedBy(): ?string
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?string $uploadedBy): void
    {
        $this->uploadedBy = $uploadedBy;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getUploads(): ?int
    {
        return $this->uploads;
    }

    public function setUploads(?int $uploads): void
    {
        $this->uploads = $uploads;
    }

    public function getHeadCounter(): ?int
    {
        return $this->headCounter;
    }

    public function setHeadCounter(?int $headCounter): void
    {
        $this->headCounter = $headCounter;
    }
}
