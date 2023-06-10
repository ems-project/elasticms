<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\DataTable;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\DataTable\Type\DataTableTypeCollection;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Routes;
use EMS\Helpers\Standard\Base64;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DataTableFactory
{
    public function __construct(
        private readonly DataTableTypeCollection $typeCollection,
        private readonly CacheItemPoolInterface $cache,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function create(string $class): TableAbstract
    {
        $type = $this->typeCollection->getByClass($class);

        $cacheKey = $this->cacheSave($class);
        $ajaxUrl = $this->generateAjaxUrl($cacheKey);

        return match (true) {
            $type instanceof AbstractEntityTableType => $this->buildEntityTable($type, $ajaxUrl),
            default => throw new \RuntimeException('Unknown dataTableType')
        };
    }

    public function createFromCache(string $cacheKey): TableAbstract
    {
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            throw new \RuntimeException('Invalid cache');
        }

        $data = $item->get();

        return $this->create($data['class']);
    }

    private function buildEntityTable(AbstractEntityTableType $type, string $ajaxUrl): EntityTable
    {
        $table = new EntityTable($type->getEntityService(), $ajaxUrl);
        $type->build($table);

        return $table;
    }

    private function generateAjaxUrl(string $cacheKey): string
    {
        return $this->urlGenerator->generate(Routes::DATA_TABLE_AJAX_TABLE, [
            'cacheKey' => $cacheKey,
        ]);
    }

    private function cacheSave(string $class): string
    {
        $key = Base64::encode($class);
        $item = $this->cache->getItem($key)->set(['class' => $class]);

        $this->cache->save($item);

        return $key;
    }
}
