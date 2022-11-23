<?php

namespace EMS\CommonBundle\Json;

class JsonMenu
{
    private string $json;
    private string $glue;
    /** @var array<mixed> */
    private $structure;
    /** @var array<string, string> */
    private array $slugs = [];
    /** @var array<string, mixed> */
    private array $bySlugs = [];
    /** @var array<mixed> */
    private array $items = [];

    public function __construct(string $source, string $glue)
    {
        $this->json = $source;
        $this->glue = $glue;
        $this->structure = \json_decode($source, true, 512, JSON_THROW_ON_ERROR);
        $this->recursiveWalk($this->structure);
    }

    /**
     * @param array<mixed> $menu
     *
     * @return string[]
     */
    private function recursiveWalk(array &$menu, string $basePath = ''): array
    {
        $contains = [];
        foreach ($menu as &$item) {
            $slug = $basePath.$item['label'];
            $this->items[$item['id']] = $item;
            $this->slugs[(string) $item['id']] = $slug;
            $this->bySlugs[$slug] = $item;
            if (isset($item['children'])) {
                $item['contains'] = $this->recursiveWalk($item['children'], $slug.$this->glue);
                $contains = \array_merge($contains, $item['contains']);
            }
            $contains[] = $item['id'];
        }

        return $contains;
    }

    /**
     * @return array<mixed>
     */
    public function getBySlug(string $slug): array
    {
        return $this->bySlugs[$slug] ?? [];
    }

    public function getSlug(string $id): ?string
    {
        return $this->slugs[$id] ?? null;
    }

    /**
     * @return array<mixed>|null
     */
    public function getItem(string $id): ?array
    {
        return $this->items[$id] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function getUids(): array
    {
        return \array_keys($this->slugs);
    }

    /**
     * @return array<int, string>
     */
    public function getSlugs(): array
    {
        return \array_values($this->slugs);
    }

    public function getJson(): string
    {
        return $this->json;
    }

    /**
     * @return array<mixed>
     */
    public function getStructure(): array
    {
        return $this->structure;
    }

    public function getGlue(): string
    {
        return $this->glue;
    }

    public function contains(string $uid): bool
    {
        return \in_array($uid, \array_keys($this->items));
    }

    /**
     * @return iterable<array<mixed>>|array<mixed>[]
     */
    public function breadcrumb(string $uid): iterable
    {
        yield from $this->yieldBreadcrumb($uid, $this->structure);
    }

    /**
     * @param array<mixed> $menu
     *
     * @return iterable<array<mixed>>|array<mixed>[]
     */
    private function yieldBreadcrumb(string $uid, array $menu): iterable
    {
        foreach ($menu as $item) {
            if ($item['id'] === $uid) {
                yield $item;
                break;
            }
            if (\in_array($uid, $item['contains'] ?? [])) {
                yield $item;
                yield from $this->yieldBreadcrumb($uid, $item['children'] ?? []);
                break;
            }
        }
    }
}
