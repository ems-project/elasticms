<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
final class TranslationsType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'entry_options' => [
                'label' => false,
            ],
            'attr' => [
                'class' => 'a2lix_lib_sf_collection',
                'data-lang-add' => t('action.add_type', ['type' => 'translation'], 'emsco-core'),
                'data-lang-remove' => t('action.remove_type', ['type' => 'translation'], 'emsco-core'),
                'data-entry-remove-class' => 'btn btn-sm btn-danger',
            ],
            'entry_type' => TranslationType::class,
            'label' => t('field.translations', [], 'emsco-core'),
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
