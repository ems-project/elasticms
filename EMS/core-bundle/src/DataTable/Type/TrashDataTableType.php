<?php

declare(strict_types=1);

namespace EMS\CoreBundle\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\Type\AbstractEntityTableType;
use EMS\CoreBundle\Form\Data\EntityTable;
use EMS\CoreBundle\Roles;
use EMS\CoreBundle\Service\TrashService;

class TrashDataTableType extends AbstractEntityTableType
{
    public function __construct(TrashService $entityService)
    {
        parent::__construct($entityService);
    }

    public function build(EntityTable $table): void
    {
    }

    public function getRoles(): array
    {
        return [Roles::ROLE_ADMIN];
    }
}
