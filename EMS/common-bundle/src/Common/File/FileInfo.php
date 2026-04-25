<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\File;

class FileInfo implements \JsonSerializable
{
    /** @var array{sha1: string, _hash: string, filesize: int, _size: int, filename: string, _name: string, mimetype: string, _type: string, _algo: string}|null */
    private ?array $fileObject = null;

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
            'file-object' => $this->fileObject,
        ];
    }
}
