<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
final class TranslationsType extends AbstractType
{
    /**
     * @param array<string,string>|null $translations
     */
    public static function getTranslation(?UserInterface $user, string $defaultTranslation, ?array $translations): string
    {
        if (null === $translations || !$user instanceof User) {
            return $defaultTranslation;
        }

        return $translations[$user->getLocalePreferred() ?? $user->getLocale()] ?? $translations[$user->getLocale()] ?? $defaultTranslation;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            $translations = $event->getData();
            if (!\is_array($translations) || \array_is_list($translations)) {
                return;
            }

            $entries = [];
            foreach ($translations as $locale => $label) {
                $entries[] = [
                    'locale' => (string) $locale,
                    'label' => $label,
                ];
            }

            $event->setData($entries);
        }, 10);

        $builder->addModelTransformer(new CallbackTransformer(
            static function (mixed $translations): array {
                if (!\is_array($translations)) {
                    return [];
                }

                if (\array_is_list($translations)) {
                    return $translations;
                }

                $entries = [];
                foreach ($translations as $locale => $label) {
                    $entries[] = [
                        'locale' => (string) $locale,
                        'label' => $label,
                    ];
                }

                return $entries;
            },
            static function (mixed $translations): array {
                if (!\is_array($translations)) {
                    return [];
                }

                $result = [];
                foreach ($translations as $translation) {
                    if (!\is_array($translation) || !isset($translation['locale'])) {
                        continue;
                    }

                    $result[(string) $translation['locale']] = $translation['label'] ?? '';
                }

                return $result;
            },
        ));
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
