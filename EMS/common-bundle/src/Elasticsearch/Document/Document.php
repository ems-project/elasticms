<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Document;

use Elastica\Result;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CoreBundle\Entity\ContentType;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Document implements DocumentInterface
{
    /** @var string */
    private $id;
    /** @var string */
    private $contentType;
    /** @var array<mixed> */
    private array $source;
    /** @var string */
    private $index;
    /** @var array<string, mixed> */
    private readonly array $raw;
    /** @var string|null */
    private $highlight;

    /**
     * @param array<mixed> $document
     */
    private function __construct($document)
    {
        $this->id = $document['_id'];
        $this->source = $document['_source'] ?? [];
        $this->index = $document['_index'];
        $this->highlight = $document['highlight'] ?? null;
        $contentType = $document['_source'][EMSSource::FIELD_CONTENT_TYPE] ?? null;
        if (null === $contentType) {
            $contentType = $document['_type'] ?? null;
            $this->source[EMSSource::FIELD_CONTENT_TYPE] = $contentType;
            @\trigger_error(\sprintf('The field %s is missing in the document %s', EMSSource::FIELD_CONTENT_TYPE, $this->getEmsId()), E_USER_DEPRECATED);
        }
        if (null === $contentType) {
            throw new \RuntimeException(\sprintf('Unable to determine the content type for document %s', $this->id));
        }
        $this->contentType = $contentType;
        $this->raw = $document;
    }

    /**
     * @param array<string, mixed> $document
     */
    public static function fromArray(array $document): Document
    {
        return new self($document);
    }

    public static function fromResult(Result $result): Document
    {
        return new self($result->getHit());
    }

    /**
     * @param array<string, mixed> $rawData
     */
    public static function fromData(ContentType|string $contentType, string $ouuid, array $rawData, string $index = 'not_available'): Document
    {
        return new self([
            '_source' => \array_merge($rawData, [
                EMSSource::FIELD_CONTENT_TYPE => \is_string($contentType) ? $contentType : $contentType->getName(),
            ]),
            '_id' => $ouuid,
            '_index' => $index,
        ]);
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id;
    }

    #[\Override]
    public function getOuuid(): string
    {
        return $this->id;
    }

    #[\Override]
    public function getContentType(): string
    {
        return $this->contentType;
    }

    #[\Override]
    public function getEmsId(): string
    {
        $id = $this->getEMSSource()->get('_version_uuid', $this->id);

        return \sprintf('%s:%s', $this->contentType, $id);
    }

    #[\Override]
    public function getEmsLink(): EMSLink
    {
        $id = $this->getEMSSource()->get('_version_uuid', $this->id);

        return EMSLink::fromContentTypeOuuid($this->contentType, $id);
    }

    #[\Override]
    public function getDocumentEmsId(): string
    {
        return EMSLink::fromContentTypeOuuid($this->contentType, $this->id)->getEmsId();
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public function getSource(bool $cleaned = false): array
    {
        if ($cleaned) {
            $source = $this->source;
            unset(
                $source[EMSSource::FIELD_CONTENT_TYPE],
                $source[EMSSource::FIELD_FINALIZATION_DATETIME],
                $source[EMSSource::FIELD_FINALIZED_BY],
                $source[EMSSource::FIELD_HASH],
                $source[EMSSource::FIELD_PUBLICATION_DATETIME],
                $source[EMSSource::FIELD_SIGNATURE]
            );

            return $source;
        }

        return $this->source;
    }

    #[\Override]
    public function getEMSSource(): EMSSourceInterface
    {
        return new EMSSource($this->source);
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    public function getHighlight(): ?string
    {
        return $this->highlight;
    }

    #[\Override]
    public function getValue(string $fieldPath, mixed $defaultValue = null): mixed
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $propertyAccessor->getValue($this->source, self::fieldPathToPropertyPath($fieldPath)) ?? $defaultValue;
    }

    #[\Override]
    public function setValue(string $fieldPath, mixed $value): self
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyAccessor->setValue($this->source, self::fieldPathToPropertyPath($fieldPath), $value);

        return $this;
    }

    public static function fieldPathToPropertyPath(string $fieldPath): string
    {
        $propertyPath = \preg_replace_callback(
            '/(?P<slug>[^\[\.]*)(?P<indexes>(\[.*\])*)\.?/',
            function ($matches) {
                if ('' === $matches['slug']) {
                    return $matches['indexes'];
                }

                return \sprintf('[%s]%s', $matches['slug'], $matches['indexes']);
            },
            $fieldPath
        );
        if (null === $propertyPath) {
            throw new \RuntimeException(\sprintf('Not able to convert the field path %s into a property path', $fieldPath));
        }

        return $propertyPath;
    }
}
