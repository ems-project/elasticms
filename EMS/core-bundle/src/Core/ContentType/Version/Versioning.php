<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\ContentType\Version;

use EMS\CoreBundle\Entity\FieldType;
use EMS\CoreBundle\Form\DataField\DateFieldType;
use EMS\Helpers\Standard\DateTime;

class Versioning
{
    private ?FieldType $rootFieldType = null;
    private ?string $fieldTag = null;
    /** @var string[] */
    private array $tags = [];

    public function __construct(
        public string $fieldFrom,
        public string $fieldTo,
    ) {
    }

    /** @param string[] $tags */
    public function enableTags(string $field, array $tags): void
    {
        $this->fieldTag = $field;
        $this->tags = $tags;
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

    public function setRootFieldType(?FieldType $rootFieldType): void
    {
        $this->rootFieldType = $rootFieldType;
    }

    public function versionDateFormat(): string
    {
        if (null === $fromFieldType = $this->rootFieldType?->findChildByName($this->fieldFrom)) {
            throw new \RuntimeException(\sprintf('Version date from "%s" field not found.', $this->fieldFrom));
        }

        if (DateFieldType::class === $fromFieldType->getType()) {
            $mappingFormat = $fromFieldType->getMappingOption('format');

            return $mappingFormat ? DateTime::convertFormat('java', $mappingFormat) : \DateTimeInterface::ATOM;
        }

        return \DateTimeInterface::ATOM;
    }
}
