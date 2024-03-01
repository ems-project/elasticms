<?php

namespace EMS\CoreBundle\Core\DataTable\Type;

use EMS\CoreBundle\Core\DataTable\DataTableFormat;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface DataTableTypeInterface
{
    /**
     * @return string[]
     */
    public function getRoles(): array;

    public function getHash(): string;

    /**
     * @return DataTableFormat[]
     */
    public function exportFormats(): array;

    /**
     * @param array<mixed> $options
     */
    public function getContext(array $options): mixed;

    public function configureOptions(OptionsResolver $optionsResolver): void;

    public function setFormat(DataTableFormat $format): void;
}
