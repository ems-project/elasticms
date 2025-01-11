<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated SendConfirmation will be removed, use numberType or HiddenType with VerificationCode validator
 */
class SendConfirmationType extends TextType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['value_field', 'token_id', 'ems_translation_domain']);
    }

    /**
     * @param FormInterface<FormInterface> $form
     * @param array<string, mixed>         $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['value_field'] = $options['value_field'];
        $view->vars['token_id'] = $options['token_id'];
        $view->vars['ems_translation_domain'] = $options['ems_translation_domain'];
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'ems_send_confirmation';
    }
}
