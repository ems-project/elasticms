<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Templating;

use EMS\ClientHelperBundle\Exception\TemplatingException;
use EMS\ClientHelperBundle\Helper\Builder\BuilderDocumentInterface;
use EMS\CommonBundle\Common\Standard\Json;

final class TemplateDocument implements BuilderDocumentInterface
{
    private string $id;
    /** @var array<mixed> */
    private array $source;
    /** @var array<string, string> */
    private array $mapping;

    public const PREFIX = '@EMSCH';

    /**
     * @param array<mixed> $source
     * @param array<mixed> $mapping
     */
    public function __construct(string $id, array $source, array $mapping)
    {
        $this->id = $id;
        $this->source = $source;
        $this->mapping = $mapping;

        if (!isset($this->source[$this->mapping['name']])) {
            throw new TemplatingException(\sprintf('Invalid EMSCH_TEMPLATES mapping %s', Json::encode($mapping)));
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->source[$this->mapping['name']];
    }

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
    public function getDataSource(): array
    {
        return [
            $this->mapping['name'] => $this->getName(),
            $this->mapping['code'] => $this->getCode(),
        ];
    }
}
