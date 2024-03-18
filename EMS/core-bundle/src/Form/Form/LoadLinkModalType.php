<?php

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Form\Field\ObjectPickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoadLinkModalType extends AbstractType
{
    public const LINK_TYPE_URL = 'url';
    public const LINK_TYPE_INTERNAL = 'internal';
    public const LINK_TYPE_FILE = 'file';
    public const LINK_TYPE_MAILTO = 'mailto';
    public const FIELD_LINK_TYPE = 'linkType';
    public const FIELD_HREF = 'href';
    public const FIELD_DATA_LINK = 'dataLink';
    public const FIELD_MAILTO = 'mailto';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_BODY = 'body';
    public const FIELD_TARGET = 'target';

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::FIELD_LINK_TYPE, ChoiceType::class, [
                'label' => 'link_modal.field.field_type',
                'required' => true,
                'choices' => [
                    'link_modal.link_type.url' => self::LINK_TYPE_URL,
                    'link_modal.link_type.internal' => self::LINK_TYPE_INTERNAL,
                    'link_modal.link_type.file' => self::LINK_TYPE_FILE,
                    'link_modal.link_type.mailto' => self::LINK_TYPE_MAILTO,
                ],
            ])
            ->add(self::FIELD_HREF, TextType::class, [
                'label' => 'link_modal.field.href',
                'required' => false,
            ])
            ->add(self::FIELD_DATA_LINK, ObjectPickerType::class, [
                'label' => 'link_modal.field.data_link',
                'required' => false,
                'multiple' => false,
            ])
            ->add(self::FIELD_MAILTO, TextType::class, [
                'label' => 'link_modal.field.mailto',
                'required' => false,
            ])
            ->add(self::FIELD_SUBJECT, TextType::class, [
                'label' => 'link_modal.field.subject',
                'required' => false,
            ])
            ->add(self::FIELD_BODY, TextareaType::class, [
                'label' => 'link_modal.field.body',
                'required' => false,
            ])
            ->add(self::FIELD_TARGET, TextType::class, [
                'label' => 'link_modal.field.target',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => EMSCoreBundle::TRANS_FORM_DOMAIN,
            ]);
        parent::configureOptions($resolver);
    }
}
