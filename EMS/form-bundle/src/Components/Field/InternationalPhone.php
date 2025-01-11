<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\InternationalPhoneNumber;
use Symfony\Component\Form\Extension\Core\Type\TelType;

final class InternationalPhone extends AbstractForgivingNumberField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'phone-international';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return TelType::class;
    }

    #[\Override]
    public function getOptions(): array
    {
        $options = parent::getOptions();

        if (\count($this->config->getChoiceList()) > 0) {
            $options['attr']['data-allowed-countries'] = \implode(',', $this->config->getChoiceList());
        }

        return $options;
    }

    #[\Override]
    public function getTransformerClasses(): array
    {
        return [InternationalPhoneNumber::class];
    }
}
