<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\User\GroupManager;
use EMS\CoreBundle\Form\Data\BoolTableColumn;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Form\Data\TableAbstract;
use EMS\CoreBundle\Routes;

class GroupDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;

    public function __construct(GroupManager $entityService)
    {
        parent::__construct($entityService);
    }

    #[\Override]
    public function build(EntityTable $table): void
    {
        $table->addColumnDefinition(new BoolTableColumn('user.index.column.enabled', 'select'));

        $this
            ->addColumnsCreatedModifiedDate($table)
            ->addColumnsOrderLabelName($table)
            ->addTableActionDelete($table, 'group_delete', 'Delete')
            ->addItemDelete($table, 'group', Routes::GROUP_DELETE)
            ->addItemEdit($table, Routes::GROUP_EDIT)
            ->addTableToolbarActionAdd($table, Routes::GROUP_ADD)
            ->addTableToolbarActionDelete($table, Routes::GROUP_DELETE_ALL);

    }
}
