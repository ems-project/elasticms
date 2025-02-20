<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Entity\Group;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use Symfony\Component\Form\AbstractType;
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
                'label' => 'group.name',
                'required' => true,
            ])
            ->add('label', TextType::class, [
                'label' => 'group.label',
            ]);

        if (self::MODE_CREATE === $mode) {
            $builder->add('create', SubmitEmsType::class, [
                'attr' => ['class' => 'btn btn-primary btn-sm'],
                'icon' => 'fa fa-plus',
            ]);
        }
        if (self::MODE_UPDATE === $mode) {
            $builder->add('update', SubmitEmsType::class, [
                'attr' => [
                    'class' => 'btn btn-primary btn-sm ',
                ],
                'icon' => 'fa fa-save',
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
