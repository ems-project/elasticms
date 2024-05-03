<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use EMS\CommonBundle\Common\EMSLink;

final class HierarchicalStructure
{
    /** @var array<mixed> */
    private array $children = [];
    /** @var mixed */
    private $data;
    private bool $active = false;

    /**
     * @param array<mixed> $source
     */
    public function __construct(private readonly string $type, private readonly string $id, private readonly array $source, ?EMSLink $activeChild = null)
    {
        if (!empty($activeChild)) {
            $this->active = $id === $activeChild->getOuuid() && $type === $activeChild->getContentType();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<mixed>
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @return array<mixed>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array<mixed> $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData(mixed $data): HierarchicalStructure
    {
        $this->data = $data;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): HierarchicalStructure
    {
        $this->active = $active;

        return $this;
    }

    public function getKey(): string
    {
        return $this->type.':'.$this->id;
    }

    public function addChild(HierarchicalStructure $child): void
    {
        $this->children[] = $child;
        if ($child->getActive()) {
            $this->setActive(true);
        }
    }
}
