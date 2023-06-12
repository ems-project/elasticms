<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\DataTable\Type;

use EMS\Helpers\Standard\Hash;

abstract class AbstractTableType implements DataTableTypeInterface
{
    /**
     * @return string[]
     */
    abstract public function getRoles(): array;

    public function getHash(): string
    {
        return Hash::string(\get_class($this));
    }
}
