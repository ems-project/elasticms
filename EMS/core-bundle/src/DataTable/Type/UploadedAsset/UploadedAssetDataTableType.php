<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type\UploadedAsset;

use EMS\CoreBundle\Core\DataTable\Type\AbstractTableType;
use EMS\CoreBundle\Core\DataTable\Type\QueryServiceTypeInterface;
use EMS\CoreBundle\Form\Data\BytesTableColumn;
use EMS\CoreBundle\Form\Data\DatetimeTableColumn;
use EMS\CoreBundle\Form\Data\QueryTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Form\Data\TranslationTableColumn;
use EMS\CoreBundle\Repository\UploadedAssetRepository;
use EMS\CoreBundle\Roles;

class UploadedAssetDataTableType extends AbstractTableType implements QueryServiceTypeInterface
{
    public const HIDE_ACTION = 'hide';

    public function __construct(
        private readonly UploadedAssetRepository $uploadedAssetRepository
    ) {
    }

    public function build(QueryTable $table): void
    {
        $table->addColumn('uploaded-file.index.column.name', 'name')
            ->setRoute('ems_file_download', function (array $data) {
                if (!\is_string($data['id'] ?? null) || !\is_string($data['type'] ?? null) || !\is_string($data['name'] ?? null)) {
                    return null;
                }

                return [
                    'sha1' => $data['id'],
                    'type' => $data['type'],
                    'name' => $data['name'],
                ];
            });
        $table->addColumnDefinition(new BytesTableColumn('uploaded-file.index.column.size', 'size'))->setCellClass('text-right');
        $table->addColumnDefinition(new TranslationTableColumn('uploaded-file.index.column.kind', 'type', 'emsco-mimetypes'));
        $table->addColumnDefinition(new DatetimeTableColumn('uploaded-file.index.column.date-added', 'created'));
        $table->addColumnDefinition(new DatetimeTableColumn('uploaded-file.index.column.date-modified', 'modified'));
        $table->setDefaultOrder('name', 'asc');

        $table->addTableAction(TableAbstract::DOWNLOAD_ACTION, 'fa fa-download', 'uploaded-file.uploaded-file.download_selected', 'uploaded-file.uploaded-file.download_selected_confirm');

        $table->addDynamicItemPostAction('ems_core_uploaded_file_hide_by_hash', 'uploaded-file.action.delete', 'trash', 'uploaded-file.delete-confirm', ['hash' => 'id'])
            ->setButtonType('outline-danger');
        $table->addTableAction(self::HIDE_ACTION, 'fa fa-trash', 'uploaded-file.delete-all', 'uploaded-file.uploaded-file.delete-all_confirm')
            ->setCssClass('btn btn-outline-danger');
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
        return true;
    }

    public function query(int $from, int $size, ?string $orderField, string $orderDirection, string $searchValue, mixed $context = null): array
    {
        return $this->uploadedAssetRepository->query($from, $size, $orderField, $orderDirection, $searchValue);
    }

    public function countQuery(string $searchValue = '', mixed $context = null): int
    {
        return $this->uploadedAssetRepository->countGroupByHashQuery($searchValue);
    }
}
