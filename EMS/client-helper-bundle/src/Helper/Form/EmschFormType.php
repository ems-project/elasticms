<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;
use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class EmschFormType extends AbstractType
{
    private const string BLOCK_FROM_CONFIG = 'emschFormConfig';

    /**
     * @param FormBuilderInterface<FormInterface>    $builder
     * @param array{ 'template': TemplateInterface } $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var list<array{ 'name': string, 'type'?: 'string', 'options': array<string, mixed> }> $elements */
        $elements = $options['template']->jsonBlock(self::BLOCK_FROM_CONFIG);

        foreach ($elements as $element) {
            $type = $element['type'] ?? 'text';

            $elementType = match ($type) {
                'text' => TextType::class,
                'date' => DateType::class,
                'button' => ButtonType::class,
                'submit' => SubmitType::class,
                default => throw new \RuntimeException(\sprintf('Unknown form type "%s"', $type)),
            };

            $builder->add(
                child: $element['name'],
                type: $elementType,
                options: $element['options'] ?? []
            );
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['template'])
            ->setAllowedTypes('template', TemplateInterface::class)
        ;
    }
}
