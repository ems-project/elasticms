<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class Hidden extends AbstractField
{
    public function getHtmlClass(): string
    {
        return 'hidden';
    }

    public function getFieldClass(): string
    {
        return HiddenType::class;
    }
}
