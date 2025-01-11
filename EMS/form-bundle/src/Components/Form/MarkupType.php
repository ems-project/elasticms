<?php

namespace EMS\FormBundle\Components\Form;

use EMS\FormBundle\FormConfig\MarkupConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkupType extends AbstractType
{
    /**
     * @param FormInterface<FormInterface> $form
     * @param array<string, mixed>         $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['config'] = $options['config'];

        parent::buildView($view, $form, $options);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('config')
            ->setAllowedTypes('config', MarkupConfig::class)
        ;
    }

    #[\Override]
    public function getParent(): string
    {
        return FormType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'ems_markup';
    }
}
