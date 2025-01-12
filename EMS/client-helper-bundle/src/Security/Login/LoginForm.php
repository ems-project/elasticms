<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Login;

use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Security\CoreApi\CoreApiAuthenticator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class LoginForm extends AbstractType
{
    public function __construct(private readonly ClientRequestManager $clientRequestManager)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'emsch.security.form.username',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'emsch.security.form.password',
                'attr' => ['autocomplete' => 'on'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'emsch.security.form.submit',
            ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return '';
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => $this->clientRequestManager->getDefault()->getCacheKey(),
            'data_class' => LoginCredentials::class,
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
            'csrf_token_id' => CoreApiAuthenticator::CSRF_ID,
        ]);
    }
}
