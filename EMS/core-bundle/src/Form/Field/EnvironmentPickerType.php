<?php

namespace EMS\CoreBundle\Form\Field;

use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Service\EnvironmentService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnvironmentPickerType extends ChoiceType
{
    /** @var array<mixed> */
    private array $environments = [];

    public function __construct(private readonly EnvironmentService $service)
    {
        parent::__construct();
    }

    public function getBlockPrefix(): string
    {
        return 'selectpicker';
    }

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];

        if ($options['userPublishEnvironments']) {
            $environments = $this->service->getUserPublishEnvironments()->toArray();
        } else {
            $environments = $this->service->getEnvironments();
        }

        $defaultEnvironment = $options['defaultEnvironment'];
        if (\is_bool($defaultEnvironment)) {
            $defaultEnvironmentIds = $this->service->getDefaultEnvironmentIds();
            $filterDefaultEnvironments = \array_filter($environments, static fn (Environment $e) => match ($defaultEnvironment) {
                true => $defaultEnvironmentIds->contains($e->getId()),
                false => !$defaultEnvironmentIds->contains($e->getId())
            });

            if (\count($filterDefaultEnvironments) > 0) {
                $environments = $filterDefaultEnvironments;
            }
        }

        foreach ($environments as $env) {
            if (($env->getManaged() || !$options['managedOnly']) && !\in_array($env->getName(), $options['ignore'], true)) {
                $choices[$env->getName()] = $env;
                $this->environments[$env->getName()] = $env;
            }
        }
        $options['choices'] = \array_map($options['choice_callback'], $choices);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->environments = [];
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'choices' => [],
                'attr' => [
                    'data-live-search' => false,
                ],
                'choice_attr' => function ($category, $key, $index) {
                    /** @var Environment $dataFieldType */
                    $dataFieldType = $this->environments[$index];

                    return [
                        'data-content' => '<span class="text-'.$dataFieldType->getColor().'"><i class="fa fa-square"></i>&nbsp;&nbsp;'.$dataFieldType->getLabel().'</span>',
                    ];
                },
                'choice_value' => fn ($value) => $value,
                'multiple' => false,
                'managedOnly' => true,
                'userPublishEnvironments' => true,
                'defaultEnvironment' => null,
                'ignore' => [],
                'choice_translation_domain' => false,
                'choice_callback' => fn (Environment $e) => $e->getName(),
            ])
            ->setAllowedTypes('defaultEnvironment', ['null', 'bool'])
        ;
    }
}
