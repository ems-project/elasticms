<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class EmailWithConfirmation extends AbstractField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'email-with-confirmation';
    }

    #[\Override]
    public function getFieldClass(): string
    {
        return RepeatedType::class;
    }

    #[\Override]
    public function getOptions(): array
    {
        $label = $this->config->getLabel() ?? '';
        $confirmLabel = \lcfirst($label);

        $options = parent::getOptions();
        $options['type'] = EmailType::class;
        $options['first_options'] = [
            'label' => $label,
            'label_attr' => $this->getLabelAttributes('_first'),
            'attr' => $options['attr'],
        ];
        $options['second_options'] = [
            'label' => 'Confirm %field%',
            'label_attr' => $this->getLabelAttributes('_second'),
            'label_translation_parameters' => ['%field%' => $confirmLabel, '%label%' => $label],
            'translation_domain' => 'validators',
            'attr' => ['class' => \sprintf('%s, repeated', $options['attr']['class'])],
        ];
        if (null !== $this->config->getPlaceholder() && '' !== $this->config->getPlaceholder()) {
            $options['second_options']['attr']['placeholder'] = $this->config->getPlaceholder();
        }
        $options['invalid_message'] = 'The "{{field1}}" and "Confirm {{field2}}" fields must match.';
        $options['invalid_message_parameters'] = [
            '{{field1}}' => $label,
            '{{field2}}' => $confirmLabel,
        ];

        return $options;
    }
}
