<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class GroupType extends AbstractType
{
    public const string MODE_CREATE = 'create';
    public const string MODE_UPDATE = 'update';
    public const string UPDATE_BUTTON = 'update_button';
    public const string CREATE_BUTTON = 'create_button';
    public const string DELETE_BUTTON = 'delete_button';
    public const string GO_TO_ADD_GROUP = 'go_to_add_group';

    public function __construct()
    {
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $mode = $options['mode'];
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
                'required' => true,
            ])
            ->add('label', TextType::class, [
                'label' => 'label',
            ]);
        if (self::MODE_CREATE === $mode) {
            $builder->add(self::CREATE_BUTTON, SubmitEmsType::class, [
                'attr' => ['class' => 'btn btn-primary btn-sm'],
                'icon' => 'fa fa-plus',
            ]);
        }
        if (self::MODE_UPDATE === $mode) {
            $builder->add(self::UPDATE_BUTTON, SubmitEmsType::class, [
                'attr' => [
                    'class' => 'btn btn-primary btn-sm ',
                ],
                'icon' => 'fa fa-save',
                'translation_domain' => EMSCoreBundle::TRANS_DOMAIN,
            ])->add(self::DELETE_BUTTON, SubmitEmsType::class, [
                'attr' => [
                    'class' => 'btn btn-primary btn-sm ',
                ],
                'icon' => 'fa fa-trash',
                'translation_domain' => EMSCoreBundle::TRANS_DOMAIN,
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Group::class,
                'translation_domain' => EMSCoreBundle::TRANS_DOMAIN,
            ])
            ->setRequired(['mode'])
            ->setAllowedValues('mode', [self::MODE_CREATE, self::MODE_UPDATE])
        ;
    }
}
