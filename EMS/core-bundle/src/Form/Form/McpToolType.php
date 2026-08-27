<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Entity\McpTool;
use EMS\CoreBundle\Form\Field\CodeEditorType;
use EMS\CoreBundle\Form\Field\ContentTypePickerType;
use EMS\CoreBundle\Form\Field\McpToolInputType;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use EMS\CoreBundle\Service\UserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
final class McpToolType extends AbstractType
{
    public function __construct(private readonly UserService $userService, private readonly TranslatorInterface $translator)
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
            ->add('inputs', CollectionType::class, [
                'label' => t('field.inputs', [], 'emsco-core'),
                'entry_type' => McpToolInputType::class,
                'entry_options' => [
                    'label' => false,
                    'row_attr' => ['class' => 'col-md-12'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'class' => 'a2lix_lib_sf_collection',
                    'data-lang-add' => $this->translator->trans('action.add', [], 'emsco-core'),
                    'data-lang-remove' => $this->translator->trans('action.remove', [], 'emsco-core'),
                    'data-entry-remove-class' => 'btn btn-sm btn-danger',
                ],
                'block_prefix' => 'inputs',
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
            ->add('template', CodeEditorType::class, [
                'label' => t('field.template', [], 'emsco-core'),
                'required' => false,
                'language' => 'ace/mode/twig',
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
            ->add('outputType', ChoiceType::class, [
                'label' => t('field.output_type', [], 'emsco-core'),
                'required' => true,
                'choices' => [
                    'Content type array' => McpTool::OUTPUT_TYPE_CONTENT_TYPE_ARRAY,
                    'Job' => McpTool::OUTPUT_TYPE_JOB,
                    'Custom' => McpTool::OUTPUT_TYPE_CUSTOM,
                ],
                'row_attr' => [
                    'class' => 'col-md-3',
                ],
            ])
            ->add('contentType', ContentTypePickerType::class, [
                'label' => t('field.content_type', [], 'emsco-core'),
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-3',
                ],
            ])
            ->add('custom_output', CodeEditorType::class, [
                'label' => t('field.custom_output', [], 'emsco-core'),
                'required' => false,
                'language' => 'ace/mode/twig',
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
