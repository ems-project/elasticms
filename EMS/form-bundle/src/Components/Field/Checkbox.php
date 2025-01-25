<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class Checkbox extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'checkbox';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return CheckboxType::class;
    }
}
