<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components;

use EMS\FormBundle\Components\Field\AbstractForgivingNumberField;
use EMS\FormBundle\Components\Field\ChoiceSelectNested;
use EMS\FormBundle\Components\Field\FieldInterface;
use EMS\FormBundle\FormConfig\AbstractFormConfig;
use EMS\FormBundle\FormConfig\ElementInterface;
use EMS\FormBundle\FormConfig\FieldConfig;
use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\FormConfigFactory;
use EMS\FormBundle\FormConfig\MarkupConfig;
use EMS\FormBundle\FormConfig\SubFormConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class Form extends AbstractType
{
    public function __construct(private readonly FormConfigFactory $configFactory)
    {
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $config = $this->getConfig($options);

        foreach ($config->getElements() as $element) {
            if ($element instanceof FieldConfig) {
                $this->addField($builder, $element, $options['data'][$element->getName()] ?? null);
            } elseif ($element instanceof MarkupConfig || $element instanceof SubFormConfig) {
                $builder->add($element->getName(), $element->getClassName(), ['config' => $element]);
            }
        }
    }

    /**
     * @param FormInterface<mixed> $form
     * @param array<string, mixed> $options
     */
    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['form_config'] = $options['config'];

        parent::buildView($view, $form, $options);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['ouuid', 'locale'])
            ->setDefaults(['config' => null, 'use_cache' => true])
            ->setNormalizer('config', fn (Options $options, $value) => $value ?: $this->configFactory->create(
                ouuid: $options['ouuid'],
                locale: $options['locale'],
                useCache: $options['use_cache']
            ))
            ->setNormalizer('attr', function (Options $options, $value) {
                if (!isset($options['config'])) {
                    return $value;
                }

                /** @var FormConfig $config */
                $config = $options['config'];
                $value['id'] = $config->getId();
                $value['class'] = $config->getLocale();

                return $value;
            })
        ;
    }

    /** @param array<string, mixed> $options */
    private function getConfig(array $options): AbstractFormConfig
    {
        if (isset($options['config'])) {
            return $options['config'];
        }

        throw new \Exception('Could not build form, config missing!');
    }

    protected function createField(FieldConfig $config): FieldInterface
    {
        $class = $config->getClassName();
        /** @var FieldInterface $field */
        $field = new $class($config);

        return $field;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param mixed|null                  $data
     */
    private function addField(FormBuilderInterface $builder, FieldConfig $element, $data): void
    {
        $field = $this->createField($element);
        $configOption = ['field_config' => $element];
        $options = ChoiceSelectNested::class !== $element->getClassName() ? $field->getOptions() : \array_merge($field->getOptions(), $configOption);
        if (null !== $data) {
            $options['data'] = $data;
        }

        $builder->add($element->getName(), $field->getFieldClass(), $options);
        $this->addModelTransformers($builder, $element, $field);
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function addModelTransformers(FormBuilderInterface $builder, ElementInterface $element, FieldInterface $field): void
    {
        if ($field instanceof AbstractForgivingNumberField) {
            $builder->get($element->getName())
            ->addModelTransformer($field->getDataTransformer());
        }
    }
}
