<?php

namespace EMS\FormBundle\Components\Form;

use EMS\FormBundle\Components\EventSubscriber\NestedChoiceEventSubscriber;
use EMS\FormBundle\Components\Field\ChoiceSelect;
use EMS\FormBundle\Components\Form;
use EMS\FormBundle\FormConfig\FieldConfig;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NestedChoiceType extends Form
{
    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var FieldConfig $config */
        $config = $this->getFieldConfig($options);
        $config->setClassName(ChoiceSelect::class);
        $choices = $config->getChoices();
        $field = $this->createField($config);
        $fieldOptions = $field->getOptions();

        if (isset($options['data']['level_0']) && \is_string($options['data']['level_0'])) {
            $fieldOptions['data'] = $options['data']['level_0'];
        }

        $builder->add('level_0', $field->getFieldClass(), $fieldOptions);

        if (null === $choices) {
            return;
        }
        $builder->addEventSubscriber(new NestedChoiceEventSubscriber($field, $choices));
    }

    /**
     * @param FormInterface<FormInterface> $form
     * @param array<string, mixed>         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['field_config'] = $options['field_config'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['field_config'])
            ->setAllowedTypes('field_config', FieldConfig::class)
        ;
    }

    /** @param array<string, mixed> $options */
    private function getFieldConfig(array $options): FieldConfig
    {
        if (isset($options['field_config'])) {
            return $options['field_config'];
        }

        throw new \Exception('Could not build form, nested choice field config missing!');
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }

    public function getBlockPrefix(): ?string
    {
        return 'ems_nested_choice';
    }
}
