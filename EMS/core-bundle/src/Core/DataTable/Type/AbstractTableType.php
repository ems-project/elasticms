<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\DataTable\Type;

use EMS\Helpers\Standard\Hash;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTableType implements DataTableTypeInterface
{
    /**
     * @return string[]
     */
    abstract public function getRoles(): array;

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    /**
     * @param array<mixed> $options
     */
    public function getContext(array $options): mixed
    {
        return null;
    }

    public function getHash(): string
    {
        return Hash::string(\get_class($this));
    }
}
