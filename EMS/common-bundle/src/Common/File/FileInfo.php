<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File;

class FileInfo implements \JsonSerializable
{
    /** @var array{sha1: string, _hash: string, filesize: int, _size: int, filename: string, _name: string, mimetype: string, _type: string, _algo: string}|null */
    private ?array $fileObject = null;
    private ?\DateTimeImmutable $firstSeen = null;
    private ?\DateTimeImmutable $lastUploaded = null;
    private ?string $name = null;
    private ?string $type = null;
    private ?string $uploadedBy = null;
    private ?bool $hidden = null;
    private ?int $size = null;
    private ?int $uploads = null;
    private ?int $headCounter = null;

    public function __construct(private readonly string $hash)
    {
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
            'uploads' => $this->uploads,
            'head-counter' => $this->headCounter,
        ];
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
