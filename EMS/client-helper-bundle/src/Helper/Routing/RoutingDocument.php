<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Helper\Builder\BuilderDocumentInterface;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\Helpers\Standard\Json;

final class RoutingDocument implements BuilderDocumentInterface
{
    private readonly string $id;
    /** @var array<mixed> */
    private array $source;

    public function __construct(DocumentInterface $document)
    {
        $this->id = $document->getId();
        $this->source = $document->getSource();

        // clean json spaces
        if (isset($this->source['config'])) {
            $this->source['config'] = Json::encode(Json::decode($this->source['config']));
        }
        if (isset($this->source['query'])) {
            $this->source['query'] = Json::encode(Json::decode($this->source['query']));
        }
    }

    #[\Override]
    public function getId(): string
    {
        return $this->id;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->source['name'];
    }

    #[\Override]
    public function getContentType(): string
    {
        return $this->source['_contenttype'];
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public function getDataSource(): array
    {
        return \array_filter([
            'name' => $this->source['name'],
            'config' => $this->source['config'] ?? null,
            'query' => $this->source['query'] ?? null,
            'index_regex' => $this->source['index_regex'] ?? null,
            'template_static' => $this->source['template_static'] ?? null,
            'template_source' => $this->source['template_source'] ?? null,
            'order' => $this->source['order'] ?? null,
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function getRouteData(): array
    {
        return \array_filter([
            'config' => $this->source['config'] ?? null,
            'query' => $this->source['query'] ?? null,
            'index_regex' => $this->source['index_regex'] ?? null,
            'template_static' => (isset($this->source['template_static']) ? \strval($this->source['template_static']) : null),
            'template_source' => (isset($this->source['template_source']) ? \strval($this->source['template_source']) : null),
        ]);
    }
}
