<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form;

use EMS\ClientHelperBundle\Helper\Form\Type as EmsFormType;
use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as SymfonyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<mixed>
 */
class EmschFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array{
     *     'template': TemplateInterface,
     *     'elements': list<array{ 'name': string, 'type'?: 'string', 'options': array<string, mixed> }>
     * } $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['elements'] as $element) {
            $elementType = $this->getElementType($element['type'] ?? 'text');
            $elementOptions = $element['options'] ?? [];
            $elementOptions['constraints'] = $this->resolveConstraints($elementOptions['constraints'] ?? []);

            $builder->add(child: $element['name'], type: $elementType, options: $elementOptions);
        }

        $template = $options['template'];
        $appendEmschFormData = function (FormEvent $event) use ($template) {
            $template->context()->append(['emschFormData' => $event->getData()]);
        };

        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, $appendEmschFormData)
            ->addEventListener(FormEvents::POST_SUBMIT, $appendEmschFormData);
    }

    /**
     * @param array{
     *     'template': TemplateInterface,
     *     'elements': list<array{ 'name': string, 'type'?: 'string', 'options': array<string, mixed> }>
     * } $options
     */
    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $template = $options['template'];
        $emschView = $template->jsonBlock(EmschFormBlock::VIEW->value);

        foreach ($options['elements'] as $element) {
            if (!isset($view->children[$element['name']])) {
                continue;
            }

            $view->children[$element['name']]->vars['emsch_form_view'] = $emschView;
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['template'])
            ->setAllowedTypes('template', TemplateInterface::class)
            ->setDefaults([
                'elements' => function (Options $options) {
                    /** @var TemplateInterface $template */
                    $template = $options['template'];

                    return $template->jsonBlock(EmschFormBlock::CONFIG->value);
                },
                'constraints' => function (Options $options) {
                    /** @var TemplateInterface $template */
                    $template = $options['template'];

                    return [new Assert\Callback(function ($data, ExecutionContextInterface $context) use ($template) {
                        $this->validateForm($context, $template);
                    })];
                },
            ])
        ;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return '';
    }

    /**
     * @return class-string<FormTypeInterface<mixed>>
     */
    private function getElementType(string $type): string
    {
        return match ($type) {
            'button' => SymfonyType\ButtonType::class,
            'checkbox' => SymfonyType\CheckboxType::class,
            'choice' => SymfonyType\ChoiceType::class,
            'choice_dynamic' => EmsFormType\EmschChoiceDynamicType::class,
            'country' => SymfonyType\CountryType::class,
            'date' => EmsFormType\EmschDateType::class,
            'datetime' => EmsFormType\EmschDateTimeType::class,
            'hidden' => SymfonyType\HiddenType::class,
            'integer' => SymfonyType\IntegerType::class,
            'language' => SymfonyType\LanguageType::class,
            'money' => SymfonyType\MoneyType::class,
            'number' => SymfonyType\NumberType::class,
            'percent' => SymfonyType\PercentType::class,
            'submit' => SymfonyType\SubmitType::class,
            'text' => SymfonyType\TextType::class,
            'textarea' => SymfonyType\TextareaType::class,
            default => throw new \RuntimeException(\sprintf('Unknown form type "%s"', $type)),
        };
    }

    private function validateForm(ExecutionContextInterface $context, TemplateInterface $template): void
    {
        /** @var array<int, array{ 'path'?: string, 'message': string }> $errors */
        $errors = $template->jsonBlock(EmschFormBlock::VALIDATE->value);

        foreach ($errors as $error) {
            $violation = $context->buildViolation($error['message']);
            if (isset($error['path'])) {
                $violation->atPath($error['path']);
            }
            $violation->addViolation();
        }
    }

    /**
     * @param array<int, array{
     *     type: string,
     *     min: ?int<0, max>,
     *     minMessage?: string,
     *     max: ?int<1, max>,
     *     maxMessage?: string,
     *     pattern: ?string,
     *     message?: string,
     *     value: ?mixed
     * }> $constraints
     *
     * @return Constraint[]
     */
    private function resolveConstraints(array $constraints): array
    {
        return \array_map(static fn (array $value) => match ($value['type']) {
            'notBlank' => new Assert\NotBlank(message: $value['message'] ?? null),
            'email' => new Assert\Email(message: $value['message'] ?? null),
            'length' => new Assert\Length(
                min: $value['min'] ?? null,
                max: $value['max'] ?? null,
                minMessage: $value['minMessage'] ?? null,
                maxMessage: $value['maxMessage'] ?? null,
            ),
            'range' => new Assert\Range(
                notInRangeMessage: $value['message'] ?? null,
                min: $value['min'] ?? null,
                max: $value['max'] ?? null,
            ),
            'regex' => new Assert\Regex(
                pattern: $value['pattern'],
                message: $value['message'] ?? null,
            ),
            'greaterThan' => new Assert\GreaterThan(value: $value['value'], message: $value['message'] ?? null),
            'greaterThanOrEqual' => new Assert\GreaterThanOrEqual(value: $value['value'], message: $value['message'] ?? null),
            'lessThan' => new Assert\LessThan(value: $value['value'], message: $value['message'] ?? null),
            'lessThanOrEqual' => new Assert\LessThanOrEqual(value: $value['value'], message: $value['message'] ?? null),
            default => throw new \RuntimeException(\sprintf('Invalid constraint type "%s"', $value['type'])),
        }, $constraints);
    }
}
