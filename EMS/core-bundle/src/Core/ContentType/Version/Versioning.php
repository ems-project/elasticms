<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\ContentType\Version;

class Versioning
{
    private ?string $fieldTag = null;
    /** @var string[] */
    private array $tags = [];

    public function __construct(
        public string $fieldFrom,
        public string $fieldTo,
        private readonly VersionOptions $options,
    ) {
    }

    public function optionDatesReadOnly(): bool
    {
        return $this->options[VersionOptions::DATES_READ_ONLY] ?? false;
    }

    /** @return string[] */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getFieldTag(): ?string
    {
        return $this->fieldTag;
    }

    /** @param string[] $tags */
    public function enableTags(string $field, array $tags): void
    {
        $this->fieldTag = $field;
        $this->tags = $tags;
    }
}
