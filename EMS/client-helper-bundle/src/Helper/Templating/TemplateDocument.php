<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Exception\TemplatingException;
use EMS\ClientHelperBundle\Helper\Builder\BuilderDocumentInterface;
use EMS\Helpers\Standard\Json;

final class TemplateDocument implements BuilderDocumentInterface
{
    public const string PREFIX = '@EMSCH';

    /**
     * @param array<mixed>          $source
     * @param array<string, string> $mapping
     */
    public function __construct(private readonly string $id, private array $source, private array $mapping)
    {
        if (!isset($this->source[$this->mapping['name']])) {
            throw new TemplatingException(\sprintf('Invalid EMSCH_TEMPLATES mapping %s', Json::encode($mapping)));
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
        return $this->source[$this->mapping['name']];
    }

    #[\Override]
    public function getContentType(): string
    {
        return $this->source['_contenttype'];
    }

    public function getCode(): string
    {
        return $this->source[$this->mapping['code']] ?? '';
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public function getDataSource(): array
    {
        return [
            $this->mapping['name'] => $this->getName(),
            $this->mapping['code'] => $this->getCode(),
        ];
    }
}
