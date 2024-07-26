<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\Form\FormManager;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Routes;

use function Symfony\Component\Translation\t;

class FormDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;

    public function __construct(FormManager $entityService)
    {
        parent::__construct($entityService);
    }

    public function build(EntityTable $table): void
    {
        $this->addColumnsOrderLabelName($table);

        $table->addItemGetAction(
            route: Routes::FORM_ADMIN_EDIT,
            labelKey: t('action.edit', [], 'emsco-core'),
            icon: 'pencil'
        );
        $table->addItemGetAction(
            route: Routes::FORM_ADMIN_REORDER,
            labelKey: t('action.reorder', [], 'emsco-core'),
            icon: 'reorder'
        );
        $table->addItemPostAction(
            route: Routes::FORM_ADMIN_DELETE,
            labelKey: t('action.delete', [], 'emsco-core'),
            icon: 'trash',
            messageKey: t('type.delete_confirm', ['type' => 'form'], 'emsco-core')
        )->setButtonType('outline-danger');

        $table->addToolbarAction(
            label: t('action.add', [], 'emsco-core'),
            icon: 'fa fa-plus',
            routeName: Routes::FORM_ADMIN_ADD
        );

        $this->addTableActionDelete($table, 'form');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
