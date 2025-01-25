<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\FileType;

class File extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'file';
    }

    #[\Override]
    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['block_prefix'] = 'ems_file';

        return $options;
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return FileType::class;
    }
}
