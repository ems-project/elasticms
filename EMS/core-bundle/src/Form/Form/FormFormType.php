<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FormFormType extends AbstractType
{
    public const ACTION_BTN_CLASS = 'action_btn_class';
    public const ACTION_ICON_CLASS = 'action_icon_class';
    public const ACTION_LABEL = 'action_label';

    public function __construct()
    {
    }

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('action', SubmitEmsType::class, [
            'label' => $options[self::ACTION_LABEL],
            'attr' => [
                'class' => $options[self::ACTION_BTN_CLASS],
            ],
            'icon' => 'fa fa-save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => EMSCoreBundle::TRANS_FORM_DOMAIN,
            self::ACTION_BTN_CLASS => 'btn btn-primary btn-sm',
            self::ACTION_ICON_CLASS => 'fa fa-save',
            self::ACTION_LABEL => 'form.form.action',
        ]);
    }
}
