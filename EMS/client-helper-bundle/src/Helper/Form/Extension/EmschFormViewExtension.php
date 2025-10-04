<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmschFormViewExtension extends AbstractTypeExtension
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        if (isset($options['emsch_form_view'])) {
            $view->vars['emsch_form_view'] = $options['emsch_form_view'];
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['emsch_form_view' => null]);
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
