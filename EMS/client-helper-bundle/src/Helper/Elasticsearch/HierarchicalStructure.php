<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Elasticsearch;

use EMS\CommonBundle\Common\EMSLink;

final class HierarchicalStructure
{
    /** @var array<mixed> */
    private array $children;
    private string $type;
    private string $id;
    /** @var array<mixed> */
    private array $source;
    /** @var mixed */
    private $data;
    private bool $active = false;

    /**
     * @param array<mixed> $source
     */
    public function __construct(string $type, string $id, array $source, EMSLink $activeChild = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->source = $source;
        $this->children = [];
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

    /**
     * @param mixed $data
     */
    public function setData($data): HierarchicalStructure
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
