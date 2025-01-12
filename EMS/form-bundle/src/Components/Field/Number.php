<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\NumberType;

class Number extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'number';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return NumberType::class;
    }
}
