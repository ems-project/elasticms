<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Entity\McpTool;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use EMS\CoreBundle\Service\UserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
final class McpToolType extends AbstractType
{
    public function __construct(private readonly UserService $userService)
    {
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', null, [
                'label' => t('field.label', [], 'emsco-core'),
                'required' => true,
                'row_attr' => [
                    'class' => 'col-md-3',
                ],
            ])
            ->add('name', null, [
                'label' => t('field.name', [], 'emsco-core'),
                'required' => true,
                'row_attr' => [
                    'class' => 'col-md-3',
                ],
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => t('field.enabled', [], 'emsco-core'),
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => t('field.roles', [], 'emsco-core'),
                'choices' => $this->userService->getExistingRoles(),
                'expanded' => true,
                'multiple' => true,
                'mapped' => true,
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => t('field.description', [], 'emsco-core'),
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
            ->add('save', SubmitEmsType::class, [
                'label' => t('action.save', [], 'emsco-core'),
                'attr' => [
                    'class' => 'btn btn-primary btn-sm ',
                    'data-testid' => 'btn-action-save',
                ],
                'icon' => 'fa fa-save',
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => McpTool::class,
        ]);
    }
}
