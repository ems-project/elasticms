<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Revision\Task;

use EMS\CoreBundle\Core\Revision\Task\DataTable\TasksDataTableContext;
use EMS\CoreBundle\Core\Revision\Task\DataTable\TasksDataTableFilters;
use EMS\CoreBundle\Entity\Task;
use EMS\CoreBundle\Form\Field\SelectUserPropertyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RevisionTaskFiltersType extends AbstractType
{
    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
            'required' => false,
            'multiple' => true,
            'attr' => ['class' => 'select2'],
            'choices' => [
                'In progress' => Task::STATUS_PROGRESS,
                'Completed' => Task::STATUS_COMPLETED,
            ],
        ]);

        if (TasksDataTableContext::TAB_USER !== $options['tab']) {
            $builder->add('assignee', SelectUserPropertyType::class, [
                'required' => false,
                'allow_add' => false,
                'multiple' => true,
                'user_property' => 'username',
                'label_property' => 'displayName',
            ]);
        }
        if (TasksDataTableContext::TAB_REQUESTER !== $options['tab']) {
            $builder->add('requester', SelectUserPropertyType::class, [
                'required' => false,
                'allow_add' => false,
                'multiple' => true,
                'user_property' => 'username',
                'label_property' => 'displayName',
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'filters';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['tab'])
            ->setDefaults([
                'method' => Request::METHOD_GET,
                'data_class' => TasksDataTableFilters::class,
                'csrf_protection' => false,
                'allow_extra_fields' => true,
                'translation_domain' => false,
            ]);
    }
}
