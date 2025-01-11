<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Json;

use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\Helpers\Standard\Base64;
use EMS\Helpers\Standard\Json;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @implements \IteratorAggregate<JsonMenuNested>
 */
final class JsonMenuNested implements \IteratorAggregate, \Countable, \Stringable
{
    private string $id;
    private string $type;
    private string $label;
    /** @var array<mixed> */
    private array $object;
    /** @var JsonMenuNested[] */
    private array $children = [];
    private ?JsonMenuNested $parent = null;
    /** @var string[] */
    private array $descendantIds = [];

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->label = \strval($data['label'] ?? '');
        $this->object = $data['object'] ?? [];

        $children = $data['children'] ?? [];
        foreach ($children as $child) {
            $childItem = new JsonMenuNested($child);
            $childItem->setParent($this);
            $this->descendantIds = [...$this->descendantIds, ...[$childItem->getId()], ...$childItem->getDescendantIds()];
            $this->children[] = $childItem;
        }
    }

    /**
     * @param array<string, mixed> $object
     */
    public static function create(string $type, array $object): JsonMenuNested
    {
        return new self([
            'id' => Uuid::uuid4()->toString(),
            'type' => $type,
            'label' => $object['label'] ?? '',
            'object' => $object,
        ]);
    }

    /**
     * @param string|array<int, array<string, mixed>> $structure
     */
    public static function fromStructure(string|array $structure): JsonMenuNested
    {
        return new self([
            'id' => '_root',
            'type' => '_root',
            'label' => '_root',
            'children' => \is_string($structure) ? Json::decode($structure) : $structure,
        ]);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->label;
    }

    /**
     * @return array{id: string, label: string, type: string, object: array<string, mixed>}
     */
    public function getData(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'object' => $this->object,
        ];
    }

    /**
     * Return a flat array.
     *
     * @return array<JsonMenuNested>
     */
    public function toArray(): array
    {
        $data = [$this];

        foreach ($this->children as $child) {
            $data = [...$data, ...$child->toArray()];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayStructure(bool $includeRoot = false): array
    {
        $children = $this->children;
        $structureChildren = \array_map(fn (JsonMenuNested $c) => $c->toArrayStructure(true), $children);

        if (!$includeRoot) {
            return $structureChildren;
        }

        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'object' => $this->object,
            'children' => \array_map(fn (JsonMenuNested $c) => $c->toArrayStructure(true), $children),
        ];
    }

    /**
     * @return \Traversable<JsonMenuNested>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->children as $child) {
            yield $child;

            if ($child->hasChildren()) {
                yield from $child;
            }
        }
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->children);
    }

    public function filterChildren(callable $callback): JsonMenuNested
    {
        $jsonMenuNested = new self(['id' => '_root', 'type' => '_root', 'label' => '_root']);
        $jsonMenuNested->setChildren($this->recursiveFilterChildren($callback));

        return $jsonMenuNested;
    }

    /**
     * Return children that are not found in passed compareJsonMenuNested OR if found but the path is different (moved).
     */
    public function diffChildren(JsonMenuNested $compareJsonMenuNested): JsonMenuNested
    {
        return $this->filterChildren(function (JsonMenuNested $child) use ($compareJsonMenuNested) {
            if (null === $compareChild = $compareJsonMenuNested->getItemById($child->getId())) {
                return true; // removed
            }

            if ($compareChild->getObject() !== $child->getObject()) {
                return true; // updated
            }

            if (\count($compareChild->getChildren()) !== \count($child->getChildren())) {
                return true; // new child
            }

            $childPath = $child->getPath(fn (JsonMenuNested $p) => $p->getId());
            $comparePath = $compareChild->getPath(fn (JsonMenuNested $p) => $p->getId());

            return $childPath !== $comparePath; // moved
        });
    }

    /**
     * @return iterable<JsonMenuNested>|JsonMenuNested[]
     */
    public function search(string $propertyPath, string $value, ?string $type = null): iterable
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->getIterator() as $child) {
            if (null !== $type && $child->getType() !== $type) {
                continue;
            }

            if (!$propertyAccessor->isReadable($child->getObject(), $propertyPath)) {
                continue;
            }

            $objectValue = $propertyAccessor->getValue($child->getObject(), $propertyPath);

            if ($objectValue === $value) {
                yield $child;
            }
        }
    }

    public function changeId(): JsonMenuNested
    {
        if ('_root' !== $this->id) {
            $this->id = Uuid::uuid4()->toString();
        }

        return $this;
    }

    public function changeIds(): JsonMenuNested
    {
        $this->changeId();

        foreach ($this->getIterator() as $child) {
            $child->changeId();
        }

        return $this;
    }

    public function getItemById(string $id): ?JsonMenuNested
    {
        foreach ($this->getIterator() as $child) {
            if ($child->getId() === $id) {
                return $child;
            }
        }

        return null;
    }

    /**
     * @throws JsonMenuNestedException
     */
    public function giveItemById(string $id): JsonMenuNested
    {
        if ($this->id === $id) {
            return $this;
        }

        if (null !== $item = $this->getItemById($id)) {
            return $item;
        }

        throw JsonMenuNestedException::itemNotFound();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array<mixed>
     */
    public function getObject(): array
    {
        return $this->object;
    }

    public function getObjectHash(): string
    {
        return Base64::encode(Json::encode([
            'object' => $this->object,
            'label' => $this->label,
        ]));
    }

    /**
     * @param JsonMenuNested[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return JsonMenuNested[]
     */
    public function getChildren(?callable $map = null): array
    {
        return $map ? \array_map($map, $this->children) : $this->children;
    }

    /**
     * @return JsonMenuNested[]
     */
    public function getPath(?callable $map = null): array
    {
        $path = [$map ? $map($this) : $this];

        if (null !== $this->parent && !$this->parent->isRoot()) {
            $path = \array_merge($this->parent->getPath($map), $path);
        }

        return $path;
    }

    public function getParent(): ?JsonMenuNested
    {
        return $this->parent;
    }

    public function giveParent(): JsonMenuNested
    {
        if (null === $this->parent) {
            throw JsonMenuNestedException::itemParentNotFound();
        }

        return $this->parent;
    }

    public function addChild(JsonMenuNested $child, ?int $position = null): JsonMenuNested
    {
        $addChild = clone $child;
        $addChild->setParent($this);

        if (null === $position) {
            $this->children[] = $addChild;
        } else {
            $children = $this->children;
            $this->children = \array_merge(
                \array_slice($children, 0, $position),
                [$addChild],
                \array_slice($children, $position)
            );
        }

        return $this;
    }

    public function removeChild(JsonMenuNested $removeChild): JsonMenuNested
    {
        $this->children = \array_filter($this->children, static fn (JsonMenuNested $child) => $child !== $removeChild);

        return $this;
    }

    public function moveChild(JsonMenuNested $child, JsonMenuNested $fromParent, JsonMenuNested $toParent, int $position): void
    {
        if (!$fromParent->hasChild($child, false)) {
            throw JsonMenuNestedException::moveChildMissing();
        }

        if ($toParent !== $fromParent && $toParent->hasChild($child, false)) {
            throw JsonMenuNestedException::moveChildExists();
        }

        $fromParent->removeChild($child);
        $toParent->addChild($child, $position);
    }

    /**
     * @param array{'id': string, 'label': ?string, 'type': string, 'children': array<mixed>} $child
     */
    public function addChildByArray(array $child): JsonMenuNested
    {
        return $this->addChild(new JsonMenuNested($child));
    }

    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    public function hasChild(JsonMenuNested $jsonMenuNested, bool $recursive = true): bool
    {
        $callback = fn (JsonMenuNested $child) => $child->getId() === $jsonMenuNested->getId();
        $children = $recursive ? $this->filterChildren($callback) : \array_filter($this->children, $callback);

        return 1 === \count($children);
    }

    public function isRoot(): bool
    {
        return null === $this->parent;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param array<string, mixed> $object
     */
    public function setObject(array $object): void
    {
        $this->object = $object;
    }

    public function setParent(?JsonMenuNested $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return string[]
     */
    public function getDescendantIds(): array
    {
        return $this->descendantIds;
    }

    /**
     * @return iterable<JsonMenuNested>
     */
    public function breadcrumb(string $uid, bool $reverseOrder = false): iterable
    {
        yield from $this->yieldBreadcrumb($uid, $this->children, $reverseOrder);
    }

    /**
     * @param string[] $paths
     */
    public function clear(array $paths): void
    {
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();

        foreach ($paths as $path) {
            $propertyAccessor->setValue($this->object, $path, null);
        }

        $this->object = \array_filter($this->object);
    }

    /**
     * @param JsonMenuNested[] $menu
     *
     * @return iterable<JsonMenuNested>
     */
    private function yieldBreadcrumb(string $uid, array $menu, bool $reverseOrder): iterable
    {
        foreach ($menu as $item) {
            if ($item->getId() === $uid) {
                yield $item;
                break;
            }
            if (\in_array($uid, $item->getDescendantIds())) {
                if (!$reverseOrder) {
                    yield $item;
                }
                yield from $this->yieldBreadcrumb($uid, $item->getChildren(), $reverseOrder);
                if ($reverseOrder) {
                    yield $item;
                }
                break;
            }
        }
    }

    /**
     * @return array<mixed>
     */
    private function recursiveFilterChildren(callable $callback): array
    {
        $result = [];

        foreach ($this->children as $child) {
            if ($callback($child)) {
                $result[] = clone $child;
            }
            if ($child->hasChildren()) {
                $result = [...$result, ...$child->recursiveFilterChildren($callback)];
            }
        }

        return $result;
    }
}
