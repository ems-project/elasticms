<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class EmailMultiple extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'email-multiple';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return TextareaType::class;
    }
}
