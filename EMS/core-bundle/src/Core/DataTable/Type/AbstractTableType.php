<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\DataTableFormat;
use EMS\Helpers\Standard\Hash;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTableType implements DataTableTypeInterface
{
    protected DataTableFormat $format = DataTableFormat::TABLE;

    /**
     * @return string[]
     */
    abstract public function getRoles(): array;

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    public function exportFormats(): array
    {
        return [];
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

    public function setFormat(DataTableFormat $format): void
    {
        $this->format = $format;
    }
}
