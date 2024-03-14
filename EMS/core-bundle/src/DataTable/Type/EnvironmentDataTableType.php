<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Form\Data\BoolTableColumn;
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
        $table->addColumn('channel.index.column.label', 'label');
        $table->addColumn('channel.index.column.name', 'name');
        $table->addColumn('channel.index.column.alias', 'alias');
        $table->addItemGetAction('ems_core_channel_edit', 'channel.actions.edit', 'pencil');
        $table->addItemPostAction('ems_core_channel_delete', 'channel.actions.delete', 'trash', 'channel.actions.delete_confirm');
        $table->addTableAction(TableAbstract::DELETE_ACTION, 'fa fa-trash', 'channel.actions.delete_selected', 'channel.actions.delete_selected_confirm');
        $table->setDefaultOrder('label');
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
