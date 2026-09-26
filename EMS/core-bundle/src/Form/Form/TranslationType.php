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
                'row_attr' => ['class' => 'col-md-2'],
                'required' => true,
                'choices' => \array_flip(Locales::getNames()),
                'choice_translation_domain' => false,
            ])
            ->add('label', TextType::class, [
                'label' => t('field.label', [], 'emsco-core'),
                'row_attr' => ['class' => 'col-md-10'],
                'required' => true,
            ]);
    }
}
