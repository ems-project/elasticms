<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class Text extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'text';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return TextType::class;
    }
}
