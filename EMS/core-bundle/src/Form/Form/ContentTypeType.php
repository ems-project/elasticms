<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Form\Field\CodeEditorType;
use EMS\CoreBundle\Form\Field\ColorPickerType;
use EMS\CoreBundle\Form\Field\IconPickerType;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<mixed>
 */
class ContentTypeType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ContentType $contentType */
        $contentType = $builder->getData();
        $environment = $contentType->giveEnvironment();

        $mapping = $options['mapping'] ?? null;
        if (null !== $mapping) {
            $builder->add('sortOrder', ChoiceType::class, [
                'required' => false,
                'label' => 'Default sort order',
                'choices' => [
                    'Ascending' => 'asc',
                    'Descending' => 'desc',
                ],
            ]);

            if ($environment->getManaged()) {
                $builder->add('versioning', ContentTypeVersioningType::class, [
                    'label' => false,
                    'mapping' => $mapping,
                ]);
            }
        }

        $builder->add('refererFieldName');
        $builder->add('editTwigWithWysiwyg', CheckboxType::class, [
            'label' => 'Edit the Twig template with a WYSIWYG editor',
            'required' => false,
        ]);
        $builder->add('webContent', CheckboxType::class, [
            'label' => 'Web content (available in WYSIWYG field as internal link)',
            'required' => false,
        ]);

        if ($environment->getManaged()) {
            $builder->add('autoPublish', CheckboxType::class, [
                'label' => t('field.auto_publish', [], 'emsco-core'),
                'required' => false,
            ]);
        }

        $builder->add('singularName', TextType::class, [
            'label' => t('field.singular_name', [], 'emsco-core'),
        ]);
        $builder->add('pluralName', TextType::class, [
            'label' => t('field.plural_name', [], 'emsco-core'),
        ]);
        $builder->add('icon', IconPickerType::class, [
            'required' => false,
        ]);
        $builder->add('color', ColorPickerType::class, [
            'label' => t('field.color', [], 'emsco-core'),
            'required' => false,
        ]);

        $builder->add('description', TextareaType::class, [
            'label' => t('field.description', [], 'emsco-core'),
            'required' => false,
            'attr' => [
                'class' => 'ems-wysiwyg',
            ],
        ]);

        if ($options['twigWithWysiwyg']) {
            $builder->add('indexTwig', TextareaType::class, [
                'label' => t('field.index_twig', [], 'emsco-core'),
                'required' => false,
                'attr' => [
                    'class' => 'ems-wysiwyg',
                    'rows' => 10,
                ],
            ]);
        } else {
            $builder->add('indexTwig', CodeEditorType::class, [
                'label' => t('field.index_twig', [], 'emsco-core'),
                'required' => false,
                'attr' => [
                ],
                'slug' => 'content_type',
            ]);
        }

        $builder->add('extra', TextareaType::class, [
            'label' => t('field.extra', [], 'emsco-core'),
            'required' => false,
            'attr' => [
                'rows' => 10,
            ],
        ]);

        $builder->add('save', SubmitEmsType::class, [
            'label' => t('action.save', [], 'emsco-core'),
            'attr' => [
                'class' => 'btn btn-primary btn-sm ',
                'data-testid' => 'btn-action-save',
            ],
            'icon' => 'fa fa-save',
        ]);
        $builder->add('saveAndUpdateMapping', SubmitEmsType::class, [
            'label' => t('action.save_update_mapping', [], 'emsco-core'),
            'attr' => [
                'class' => 'btn btn-primary btn-sm ',
                'data-testid' => 'btn-action-save-update-mapping',
            ],
            'icon' => 'fa fa-save',
        ]);
        $builder->add('saveAndClose', SubmitEmsType::class, [
            'label' => t('action.save_close', [], 'emsco-core'),
            'attr' => [
                'class' => 'btn btn-primary btn-sm ',
                'data-testid' => 'btn-action-save-close',
            ],
            'icon' => 'fa fa-save',
        ]);

        $builder->add('rootContentType');

        $builder->add('roles', ContentTypeRolesType::class, [
            'managed' => $environment->getManaged(),
            'label' => false,
        ]);
        $builder->add('settings', ContentTypeSettingsType::class, [
            'label' => false,
        ]);

        if (null !== $mapping) {
            $builder->add('fields', ContentTypeFieldsType::class, [
                'label' => false,
                'mapping' => $mapping,
            ]);
        }

        if ($environment->getManaged()) {
            $builder->add('defaultValue', CodeEditorType::class, [
                'label' => t('field.default_value', [], 'emsco-core'),
                'required' => false,
            ])->add('askForOuuid', CheckboxType::class, [
                'label' => 'Ask for OUUID',
                'required' => false,
            ]);
            $builder->add('saveAndEditStructure', SubmitEmsType::class, [
                'label' => t('action.save_and_edit_structure', [], 'emsco-core'),
                'attr' => [
                    'class' => 'btn btn-primary btn-sm ',
                    'data-testid' => 'btn-action-save-edit-structure',
                ],
                'icon' => 'fa fa-save',
            ]);
            $builder->add('saveAndReorder', SubmitEmsType::class, [
                'label' => t('action.save_and_reorder', [], 'emsco-core'),
                'attr' => [
                    'class' => 'btn btn-primary btn-sm ',
                    'data-testid' => 'btn-action-save-reoder',
                ],
                'icon' => 'fa fa-reorder',
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('twigWithWysiwyg', true);
        $resolver->setDefault('mapping', null);
    }
}
