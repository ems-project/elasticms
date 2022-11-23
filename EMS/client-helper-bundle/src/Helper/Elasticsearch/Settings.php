<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use EMS\ClientHelperBundle\Helper\ContentType\ContentType;

final class Settings
{
    private ?string $routeContentTypeName;
    private ?ContentType $routeContentType;

    private ?string $translationContentTypeName;
    private ?ContentType $translationContentType;

    /** @var ContentType[] */
    private array $templateContentTypes = [];
    /** @var string[] */
    private array $templateContentTypeNames = [];
    /** @var array<mixed> */
    private array $templateMapping = [];

    public function addRouting(string $contentTypeName, ?ContentType $contentType): void
    {
        $this->routeContentTypeName = $contentTypeName;
        $this->routeContentType = $contentType;
    }

    public function addTranslation(string $contentTypeName, ?ContentType $contentType): void
    {
        $this->translationContentTypeName = $contentTypeName;
        $this->translationContentType = $contentType;
    }

    /**
     * @param array<mixed> $mapping
     */
    public function addTemplating(string $contentTypeName, array $mapping, ?ContentType $contentType): void
    {
        $this->templateContentTypeNames[] = $contentTypeName;
        $this->templateMapping[$contentTypeName] = $mapping;

        if ($contentType) {
            $this->templateContentTypes[$contentTypeName] = $contentType;
        }
    }

    /**
     * @return ContentType[]
     */
    public function getContentTypes(): array
    {
        return \array_filter([
            $this->translationContentType,
            $this->routeContentType,
            ...\array_values($this->templateContentTypes),
        ]);
    }

    public function getTemplateContentType(string $contentTypeName): ContentType
    {
        if (!isset($this->templateContentTypes[$contentTypeName])) {
            throw new \RuntimeException('Missing config EMSCH_TEMPLATES');
        }

        return $this->templateContentTypes[$contentTypeName];
    }

    /**
     * @return ContentType[]
     */
    public function getTemplateContentTypes(): array
    {
        return $this->templateContentTypes;
    }

    /**
     * @return string[]
     */
    public function getTemplateContentTypeNames(): array
    {
        return $this->templateContentTypeNames;
    }

    /**
     * @return array<mixed>
     */
    public function getTemplateMapping(string $contentTypeName): array
    {
        if (!isset($this->templateMapping[$contentTypeName])) {
            throw new \RuntimeException('Missing config EMSCH_TEMPLATES');
        }

        return $this->templateMapping[$contentTypeName];
    }

    public function getTranslationContentType(): ?ContentType
    {
        return $this->translationContentType;
    }

    public function getTranslationContentTypeName(): ?string
    {
        return $this->translationContentTypeName;
    }

    public function getRoutingContentType(): ?ContentType
    {
        return $this->routeContentType;
    }

    public function getRouteContentTypeName(): ?string
    {
        return $this->routeContentTypeName;
    }
}
