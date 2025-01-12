<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BelgiumPhoneNumber;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class Phone extends AbstractForgivingNumberField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'phone';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return TelType::class;
    }

    #[\Override]
    public function getTransformerClasses(): array
    {
        return [BelgiumPhoneNumber::class];
    }
}
