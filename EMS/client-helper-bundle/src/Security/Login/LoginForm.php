<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Login;

use EMS\ClientHelperBundle\Security\CoreApi\CoreApiAuthenticator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('password', PasswordType::class, [
                'attr' => ['autocomplete' => 'on'],
            ])
            ->add('submit', SubmitType::class);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LoginCredentials::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
            'csrf_token_id' => CoreApiAuthenticator::CSRF_ID,
        ]);
    }
}
