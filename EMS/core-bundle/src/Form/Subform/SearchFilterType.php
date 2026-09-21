<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Subform;

use EMS\CoreBundle\Entity\Form\SearchFilter;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
class SearchFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['is_super'] || empty($options['searchFields'])) {
            $builder->add('field', TextType::class, [
                'label' => t('field.field', [], 'emsco-core'),
                'required' => false,
            ]);
        } else {
            $builder->add('field', ChoiceType::class, [
                'label' => t('field.field', [], 'emsco-core'),
                'choices' => $options['searchFieldsData'],
                'required' => false,
                'choice_translation_domain' => false,
                'choice_attr' => function ($category, $key, $index) use ($options) {
                    $searchFieldOption = $options['searchFields'][$key];

                    return [
                        'data-content-types' => Json::encode($searchFieldOption['contentTypes']),
                        'data-operators' => Json::encode($searchFieldOption['operators']),
                    ];
                },
            ]);
        }

        $builder->add('boost', $options['is_super'] ? NumberType::class : HiddenType::class, [
            'label' => t('field.boost', [], 'emsco-core'),
            'required' => false,
        ]);

        $builder->add('operator', ChoiceType::class, [
            'label' => t('field.operator', [], 'emsco-core'),
            'choices' => [
                'query_and' => 'query_and',
                'query_or' => 'query_or',
                'match_and' => 'match_and',
                'match_or' => 'match_or',
                'term' => 'term',
                'prefix' => 'prefix',
                'match_phrase' => 'match_phrase',
                'match_phrase_prefix' => 'match_phrase_prefix',
                'gt' => 'gt',
                'gte' => 'gte',
                'lt' => 'lt',
                'lte' => 'lte',
            ],
            'choice_label' => fn (string $value, string $label) => match ($value) {
                'query_and' => t('key.query_and', [], 'emsco-core'),
                'query_or' => t('key.query_or', [], 'emsco-core'),
                'match_and' => t('key.match_and', [], 'emsco-core'),
                'match_or' => t('key.match_or', [], 'emsco-core'),
                'term' => t('key.term', [], 'emsco-core'),
                'prefix' => t('key.prefix', [], 'emsco-core'),
                'match_phrase' => t('key.match_phrase', [], 'emsco-core'),
                'match_phrase_prefix' => t('key.match_phrase_prefix', [], 'emsco-core'),
                'gt' => t('key.gt', [], 'emsco-core'),
                'gte' => t('key.gte', [], 'emsco-core'),
                'lt' => t('key.lt', [], 'emsco-core'),
                'lte' => t('key.lte', [], 'emsco-core'),
                default => throw new \RuntimeException(\sprintf('Unknown operator "%s"', $value)),
            },
        ]);

        $builder->add('booleanClause', ChoiceType::class, [
            'label' => t('field.boolean_clause', [], 'emsco-core'),
            'choices' => [
                'must' => 'must',
                'should' => 'should',
                'must_not' => 'must_not',
                'filter' => 'filter',
            ],
            'choice_label' => fn (string $value, string $label) => match ($value) {
                'must' => t('key.must', [], 'emsco-core'),
                'should' => t('key.should', [], 'emsco-core'),
                'must_not' => t('key.must_not', [], 'emsco-core'),
                'filter' => t('key.filter', [], 'emsco-core'),
                default => throw new \RuntimeException(\sprintf('Unknown boolean clause "%s"', $value)),
            },
        ]);

        $builder->add('pattern', TextType::class, [
            'label' => t('field.pattern', [], 'emsco-core'),
            'required' => false,
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchFilter::class,
            'is_super' => false,
            'searchFields' => [],
            'searchFieldsData' => [],
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'search_filter';
    }
}
