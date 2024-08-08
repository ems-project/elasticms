<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\Environment;

use EMS\CoreBundle\Core\DataTable\ArrayDataSource;
use EMS\CoreBundle\Core\DataTable\Type\AbstractTableType;
use EMS\CoreBundle\Core\DataTable\Type\QueryServiceTypeInterface;
use EMS\CoreBundle\DataTable\Type\DataTableTypeTrait;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\AliasService;

use function Symfony\Component\Translation\t;

class EnvironmentOrphanIndexDataTableType extends AbstractTableType implements QueryServiceTypeInterface
{
    use DataTableTypeTrait;

    public function __construct(private readonly AliasService $aliasService)
    {
    }

    public function build(QueryTable $table): void
    {
        $table->setIdField('name');
        $table->setDefaultOrder('name')->setLabelAttribute('name');

        $table->addColumn(t('field.name', [], 'emsco-core'), 'name');
        $table->addColumn(t('field.count', [], 'emsco-core'), 'count');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }

    public function getQueryName(): string
    {
        return 'EnvironmentOrphanIndex';
    }

    public function isSortable(): bool
    {
        return false;
    }

    public function query(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        $dataSource = $this->getDataSource($searchValue);

        if (null !== $orderField) {
            return $dataSource->sort(\sprintf('[%s]', $orderField), $orderDirection)->data;
        }

        return $dataSource->data;
    }

    public function countQuery(string $searchValue = '', mixed $context = null): int
    {
        return $this->getDataSource($searchValue)->count();
    }

    private function getDataSource(string $searchValue): ArrayDataSource
    {
        static $dataSource = null;

        if (null === $dataSource) {
            $dataSource = new ArrayDataSource($this->aliasService->getOrphanIndexes());
        }

        return $dataSource->search($searchValue);
    }
}
