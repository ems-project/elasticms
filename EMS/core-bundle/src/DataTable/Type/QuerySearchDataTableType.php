<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\QuerySearchService;

use function Symfony\Component\Translation\t;

class QuerySearchDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;

    public function __construct(QuerySearchService $entityService)
    {
        parent::__construct($entityService);
    }

    public function build(EntityTable $table): void
    {
        $table->setDefaultOrder('label')->setLabelAttribute('label');

        $table->addColumn(t('field.label', [], 'emsco-core'), 'label');
        $table->addColumn(t('field.name', [], 'emsco-core'), 'name');

        $this->addItemEdit($table, 'ems_core_query_search_edit');

        $table->addItemPostAction(
            route: 'ems_core_query_search_delete',
            labelKey: t('action.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('type.delete_confirm', ['type' => 'query_search'], 'emsco-core')
        )->setButtonType('outline-danger');

        $this
            ->addTableToolbarActionAdd($table, 'ems_core_query_search_add')
            ->addTableActionDelete($table, 'query_search');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
