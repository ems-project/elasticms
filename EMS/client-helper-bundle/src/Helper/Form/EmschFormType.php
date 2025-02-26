<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form;

use EMS\ClientHelperBundle\Helper\Form\Type as EmsFormType;
use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<mixed>
 */
class EmschFormType extends AbstractType
{
    private const string BLOCK_FROM_CONFIG = 'emschFormConfig';

    /**
     * @param FormBuilderInterface<mixed>            $builder
     * @param array{ 'template': TemplateInterface } $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var list<array{ 'name': string, 'type'?: 'string', 'options': array<string, mixed> }> $elements */
        $elements = $options['template']->jsonBlock(self::BLOCK_FROM_CONFIG);

        foreach ($elements as $element) {
            $elementType = $this->getElementType($element['type'] ?? 'text');
            $elementOptions = $element['options'] ?? [];
            $elementOptions['constraints'] = $this->resolveConstraints($elementOptions['constraints'] ?? []);

            $builder->add(child: $element['name'], type: $elementType, options: $elementOptions);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['template'])
            ->setAllowedTypes('template', TemplateInterface::class);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return '';
    }

    private function getElementType(string $type): string
    {
        return match ($type) {
            'button' => Type\ButtonType::class,
            'checkbox' => Type\CheckboxType::class,
            'choice' => Type\ChoiceType::class,
            'choice_dynamic' => EmsFormType\EmschChoiceDynamicType::class,
            'country' => Type\CountryType::class,
            'date' => EmsFormType\EmschDateType::class,
            'datetime' => EmsFormType\EmschDateTimeType::class,
            'hidden' => Type\HiddenType::class,
            'integer' => Type\IntegerType::class,
            'language' => Type\LanguageType::class,
            'money' => Type\MoneyType::class,
            'number' => Type\NumberType::class,
            'submit' => Type\SubmitType::class,
            'text' => Type\TextType::class,
            'textarea' => Type\TextareaType::class,
            default => throw new \RuntimeException(\sprintf('Unknown form type "%s"', $type)),
        };
    }

    /**
     * @param array<int, array<mixed>> $constraints
     *
     * @return Constraint[]
     */
    private function resolveConstraints(array $constraints): array
    {
        return \array_map(static fn (array $value) => match ($value['type']) {
            'notBlank' => new Assert\NotBlank(message: $value['message'] ?? null),
            'email' => new Assert\Email(message: $value['message'] ?? null),
            'length' => new Assert\Length(
                min: isset($value['min']) ? (int) $value['min'] : null,
                max: isset($value['max']) ? (int) $value['max'] : null,
                minMessage: $value['minMessage'] ?? null,
                maxMessage: $value['maxMessage'] ?? null,
            ),
            default => throw new \RuntimeException(\sprintf('Invalid constraint type "%s"', $value['type'])),
        }, $constraints);
    }
}
