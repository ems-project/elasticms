<?php

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\UserService;

class UserGroupDataTableType extends AbstractEntityTableType
{
    use DataTableTypeTrait;
    public function __construct(
        UserService $entityService,
        ) {
        parent::__construct($entityService);
    }
    #[\Override]
    public function build(EntityTable $table): void
    {
        $this
            ->addColumnsOrderLabelName($table)
            ->addItemDelete($table, 'userGroup', Routes::GROUP_DELETE);
    }
}