<?php

namespace EMS\CoreBundle\Core\DataTable\Type;

interface DataTableTypeInterface
{
    /**
     * @return string[]
     */
    public function getRoles(): array;

    public function getHash(): string;
}
