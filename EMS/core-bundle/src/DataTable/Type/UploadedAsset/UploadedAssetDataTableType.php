<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\UploadedAsset;

use Doctrine\ORM\QueryBuilder;
use EMS\CoreBundle\Core\DataTable\Type\AbstractTableType;
use EMS\CoreBundle\Core\DataTable\Type\QueryServiceTypeInterface;
use EMS\CoreBundle\Form\Data\BytesTableColumn;
use EMS\CoreBundle\Form\Data\DatetimeTableColumn;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Data\TranslationTableColumn;
use EMS\CoreBundle\Repository\UploadedAssetRepository;
use EMS\CoreBundle\Roles;
use EMS\Helpers\Standard\Type;

use function Symfony\Component\Translation\t;

class UploadedAssetDataTableType extends AbstractTableType implements QueryServiceTypeInterface
{
    public const HIDE_ACTION = 'hide';

    public function __construct(
        private readonly UploadedAssetRepository $uploadedAssetRepository
    ) {
    }

    public function build(QueryTable $table): void
    {
        $table->setDefaultOrder('name')->setLabelAttribute('name');

        $columnName = $table->addColumn(t('field.name', [], 'emsco-core'), 'name');
        $columnName->setRoute('ems_file_download', function (array $data) {
            try {
                return [
                    'sha1' => Type::string($data['id']),
                    'type' => Type::string($data['type']),
                    'name' => Type::string($data['name']),
                ];
            } catch (\Throwable) {
                return null;
            }
        });

        $table->addColumnDefinition(new BytesTableColumn(
            titleKey: t('field.file.size', [], 'emsco-core'),
            attribute: 'size'
        ))->setCellClass('text-right');
        $table->addColumnDefinition(new TranslationTableColumn(
            titleKey: t('field.file.type', [], 'emsco-core'),
            attribute: 'type',
            domain: 'emsco-mimetypes'
        ));
        $table->addColumnDefinition(new DatetimeTableColumn(
            titleKey: t('field.created', [], 'emsco-core'),
            attribute: 'created'
        ));
        $table->addColumnDefinition(new DatetimeTableColumn(
            titleKey: t('field.modified', [], 'emsco-core'),
            attribute: 'modified'
        ));

        $table->addDynamicItemPostAction(
            route: 'ems_core_uploaded_file_hide_by_hash',
            labelKey: t('action.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('type.delete_confirm', ['type' => 'uploaded_file'], 'emsco-core'),
            routeParameters: ['hash' => 'id']
        )->setButtonType('outline-danger');

        $table->addTableAction(
            name: TableAbstract::DOWNLOAD_ACTION,
            icon: 'fa fa-download',
            labelKey: t('action.download_selected', [], 'emsco-core')
        );
        $table->addTableAction(
            name: self::HIDE_ACTION,
            icon: 'fa fa-trash',
            labelKey: t('action.delete_selected', [], 'emsco-core'),
            confirmationKey: t('type.delete_selected_confirm', ['type' => 'uploaded_file'], 'emsco-core'),
        )->setCssClass('btn btn-outline-danger');
    }

    public function getQueryName(): string
    {
        return 'uploaded_asset';
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_PUBLISHER];
    }

    public function isQuerySortable(): bool
    {
        return false;
    }

    public function query(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        $qb = $this->createQueryBuilder($searchValue);
        $qb
            ->select('ua.sha1 as id')
            ->addSelect('max(ua.name) as name')
            ->addSelect('max(ua.size) as size')
            ->addSelect('max(ua.type) as type')
            ->addSelect('min(ua.created) as created')
            ->addSelect('max(ua.modified) as modified')
            ->groupBy('ua.sha1')
            ->setFirstResult($from)
            ->setMaxResults($size);

        if (null !== $orderField) {
            $qb->orderBy($orderField, $orderDirection);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function countQuery(string $searchValue = '', mixed $context = null): int
    {
        return (int) $this->createQueryBuilder($searchValue)
            ->select('count(DISTINCT ua.sha1)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createQueryBuilder(string $searchValue = ''): QueryBuilder
    {
        return $this->uploadedAssetRepository->makeQueryBuilder($searchValue);
    }
}
