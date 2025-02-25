<?php

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Core\Form\FormManager;
use EMS\CoreBundle\Core\User\GroupManager;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Routes;
use function Symfony\Component\Translation\t;

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
        dump($table);
        $this
            ->addColumnsOrderLabelName($table)
            ->addTableActionDelete($table, 'group_delete');

    }

}