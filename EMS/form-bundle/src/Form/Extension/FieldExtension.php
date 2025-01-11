<?php

declare(strict_types=1);

namespace EMS\FormBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FieldExtension extends AbstractTypeExtension
{
    /**
     * @param FormInterface<FormInterface> $form
     * @param array<string, mixed>         $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['ems_config_id'] = $options['ems_config_id'];
        $view->vars['ems_translation_domain'] = $options['ems_translation_domain'];
        $view->vars['ems_meta'] = $options['ems_meta'];
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'ems_config_id' => null,
            'ems_translation_domain' => null,
            'ems_meta' => [],
        ]);
    }

    /** @return string[] */
    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class, SubmitType::class];
    }
}
