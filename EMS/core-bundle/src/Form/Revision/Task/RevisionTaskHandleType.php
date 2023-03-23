<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Revision\Task;

use EMS\CoreBundle\Entity\Task;
use EMS\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RevisionTaskHandleType extends AbstractType
{
    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Task $task */
        $task = $options['task'];
        /** @var User $user */
        $user = $options['user'];

        $builder->add('comment', TextareaType::class, [
            'attr' => ['rows' => 4],
            'constraints' => 'approve' !== $options['handle'] ? [new NotBlank()] : [],
        ]);

        if ($task->isOpen() && $task->isAssignee($user)) {
            $builder->add('send', ButtonType::class);
        }

        if (!$task->isOpen() && (!$task->isAssignee($user) || $task->isRequester($user))) {
            $builder
                ->add('approve', ButtonType::class)
                ->add('reject', ButtonType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['task', 'user', 'handle'])
            ->setAllowedValues('handle', [null, 'send', 'approve', 'reject'])
        ;
    }
}
