<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
final class McpToolInputType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('field.name', [], 'emsco-core'),
                'required' => true,
                'row_attr' => ['class' => 'col-md-3'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => t('field.type', [], 'emsco-core'),
                'required' => true,
                'choices' => [
                    'string' => 'string',
                    'integer' => 'integer',
                    'number' => 'number',
                    'boolean' => 'boolean',
                ],
                'row_attr' => ['class' => 'col-md-3'],
            ])
            ->add('description', TextareaType::class, [
                'label' => t('field.description', [], 'emsco-core'),
                'required' => false,
                'row_attr' => ['class' => 'col-md-3'],
            ])
            ->add('example', TextType::class, [
                'label' => t('field.example', [], 'emsco-core'),
                'required' => false,
                'row_attr' => ['class' => 'col-md-3'],
            ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'mcp_tool_input';
    }
}
