<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\FileType;

class File extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'file';
    }

    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['block_prefix'] = 'ems_file';

        return $options;
    }

    public function getFieldClass(): string
    {
        return FileType::class;
    }
}
