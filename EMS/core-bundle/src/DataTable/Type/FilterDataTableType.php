<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\Mapping\FilterManager;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Routes;

use function Symfony\Component\Translation\t;

class FilterDataTableType extends AbstractEntityTableType
{
    public function __construct(FilterManager $filterManager)
    {
        parent::__construct($filterManager);
    }

    public function build(EntityTable $table): void
    {
        $table->setDefaultOrder('orderKey')->setLabelAttribute('label');

        $table->addColumn(t('key.loop_count', [], 'emsco-core'), 'orderKey');
        $table->addColumn(t('field.label', [], 'emsco-core'), 'label');
        $table->addColumn(t('field.name', [], 'emsco-core'), 'name');

        $table->addItemGetAction(
            route: Routes::FILTER_EDIT,
            labelKey: t('action.edit', [], 'emsco-core'),
            icon: 'pencil'
        );
        $table->addItemGetAction(
            route: Routes::FILTER_EXPORT,
            labelKey: t('action.export', [], 'emsco-core'),
            icon: 'sign-out'
        );
        $table->addItemPostAction(
            route: Routes::FILTER_DELETE,
            labelKey: t('action.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('type.delete_confirm', ['type' => 'filter'], 'emsco-core')
        )->setButtonType('outline-danger');

        $table->addToolbarAction(
            label: t('action.add', [], 'emsco-core'),
            icon: 'fa fa-plus',
            routeName: Routes::FILTER_ADD
        );
        $table->addTableAction(
            name: TableAbstract::DELETE_ACTION,
            icon: 'fa fa-trash',
            labelKey: t('action.delete_selected', [], 'emsco-core'),
            confirmationKey: t('type.delete_selected_confirm', ['type' => 'filter'], 'emsco-core')
        )->setCssClass('btn btn-outline-danger');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
