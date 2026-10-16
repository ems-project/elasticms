<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form\ChoiceList;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ChoiceListOptionChoiceType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', TextType::class, ['required' => true])
            ->add('group', ChoiceType::class, [
                'choices' => [] === $options['groups'] ? [] : \array_combine($options['groups'], $options['groups']),
                'required' => false,
                'placeholder' => '',
            ])
            ->add('labels', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['required' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options): void {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }

            $labels = \is_array($data['labels'] ?? null) ? $data['labels'] : [];
            $data['labels'] = \array_replace(\array_fill_keys($options['locales'], ''), $labels);
            $event->setData($data);
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locales' => [],
            'groups' => [],
        ]);
        $resolver->setAllowedTypes('locales', 'string[]');
        $resolver->setAllowedTypes('groups', 'string[]');
    }
}
