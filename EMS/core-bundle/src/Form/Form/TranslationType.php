<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Locales;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
final class TranslationType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'label' => t('field.locale', [], 'emsco-core'),
                'row_attr' => ['class' => 'col-md-3'],
                'required' => true,
                'choices' => \array_flip(Locales::getNames()),
                'choice_translation_domain' => false,
            ])
            ->add('label', $options['label_type'], [
                'label' => t('field.label', [], 'emsco-core'),
                'row_attr' => ['class' => 'col-md-6'],
                'required' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => t('field.gender', [], 'emsco-core'),
                'row_attr' => ['class' => 'col-md-3'],
                'required' => false,
                'choices' => [
                    t('key.gender.male', [], 'emsco-core')->getMessage() => 'male',
                    t('key.gender.female', [], 'emsco-core')->getMessage() => 'female',
                    t('key.gender.neutral', [], 'emsco-core')->getMessage() => 'neutral',
                ],
                'choice_translation_domain' => 'emsco-core',
            ]);
    }
}
