<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\EnvironmentService;

class EnvironmentDataTableType extends AbstractEntityTableType
{
    public function __construct(EnvironmentService $entityService)
    {
        parent::__construct($entityService);
    }

    public function build(EntityTable $table): void
    {
        $table->addColumn('table.index.column.loop_count', 'orderKey');
        $table->addColumn('environment.index.column.label', 'label');
        $table->addColumn('environment.index.column.name', 'name');
        $table->addColumn('environment.index.column.alias', 'alias');
        $table->addColumn('environment.index.column.total_indexed_label', 'total');
        $table->addColumn('environment.index.column.total_in_ems', 'counter');
        $table->addColumn('environment.index.column.total_mark_has_deleted', 'deletedRevision');
        $table->addItemGetAction('ems_core_environment_rebuild', 'environment.actions.rebuild_button', 'recycle');
        $table->addItemGetAction('ems_core_environment_view', 'environment.actions.view_button', 'eye');
        $table->addItemGetAction('ems_core_environment_edit', 'environment.actions.edit_button', 'edit');
        $table->addItemPostAction('ems_core_environment_delete', 'environment.actions.delete', 'trash', 'environment.actions.delete_confirm');
        $table->addTableAction(TableAbstract::DELETE_ACTION, 'fa fa-trash', 'environment.actions.delete_selected', 'environment.actions.delete_selected_confirm');
        $table->setDefaultOrder('label');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
