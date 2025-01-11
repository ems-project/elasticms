<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class Hidden extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'hidden';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return HiddenType::class;
    }
}
