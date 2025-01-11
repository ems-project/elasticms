<?php

namespace App\CLI\Client\Photos;

class Photo
{
    private const string DATETIME_FORMAT = 'c';
    /** @var mixed[]|null */
    private ?array $previewFile = null;
    /**
     * @var mixed[]|null
     */
    private ?array $originalFile = null;
    /** @var mixed[][] */
    private array $memberOf = [];
    private ?string $modificationDate = null;
    private ?string $addedDate = null;
    /** @var mixed[]|null */
    private ?array $location = null;

    public function __construct(private readonly string $libraryType, private readonly string $source, private readonly string $ouuid, private readonly string $filename)
    {
    }

    public function getOuuid(): string
    {
        return $this->ouuid;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return \array_filter([
            'library_type' => $this->libraryType,
            'source' => $this->source,
            'filename' => $this->filename,
            'preview_file' => $this->previewFile,
            'original_file' => $this->originalFile,
            'member_of' => $this->memberOf,
            'modification_date' => $this->modificationDate,
            'added_date' => $this->addedDate,
            'location' => $this->location,
        ]);
    }

    /**
     * @param mixed[] $previewFile
     */
    public function setPreviewFile(array $previewFile): void
    {
        $this->previewFile = $previewFile;
    }

    /**
     * @param mixed[] $originalFile
     */
    public function setOriginalFile(array $originalFile): void
    {
        $this->originalFile = $originalFile;
    }

    /**
     * @param mixed[] $memberOf
     */
    public function addMemberOf(array $memberOf): void
    {
        $this->memberOf = \array_merge($this->memberOf, $memberOf);
    }

    public function setModificationDate(\DateTimeImmutable $dateTime): void
    {
        $this->modificationDate = $dateTime->format(self::DATETIME_FORMAT);
    }

    public function setAddedDate(\DateTimeImmutable $dateTime): void
    {
        $this->addedDate = $dateTime->format(self::DATETIME_FORMAT);
    }

    public function setLocationPoint(float $latitude, float $longitude): void
    {
        $this->location = [
            'lat' => $latitude,
            'lon' => $longitude,
        ];
    }
}
