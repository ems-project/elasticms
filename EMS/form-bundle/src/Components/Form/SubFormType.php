<?php

namespace EMS\FormBundle\Components\Form;

use EMS\FormBundle\Components\Form;
use EMS\FormBundle\FormConfig\SubFormConfig;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubFormType extends Form
{
    /**
     * @param FormInterface<FormInterface> $form
     * @param array<string, mixed>         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['config'] = $options['config'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('config')
            ->setAllowedTypes('config', SubFormConfig::class)
        ;
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }

    public function getBlockPrefix(): ?string
    {
        return 'ems_subform';
    }
}
