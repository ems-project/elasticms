<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Field;

use EMS\CoreBundle\Entity\Form\AssetEntity;
use EMS\CoreBundle\Form\DataTransformer\AssetTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditImageType extends AbstractType
{
    public const FIELD_FILENAME = 'filename';
    public const FIELD_HASH = 'hash';

    public function __construct(
        private readonly AssetTransformer $transformer,
    ) {
    }

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer($this->transformer);
        $builder->add(self::FIELD_FILENAME, TextType::class);
        $builder->add(self::FIELD_HASH, TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AssetEntity::class,
        ]);
    }
}
