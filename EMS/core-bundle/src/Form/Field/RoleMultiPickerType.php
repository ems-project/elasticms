<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Field;

use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Service\UserService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class RoleMultiPickerType extends ChoiceType
{
    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label' => t('field.roles', [], 'emsco-core'),
            'choices' => $this->userService->listUserRoles(),
            'multiple' => true,
            'expanded' => true,
            'translation_domain' => EMSCoreBundle::TRANS_FORM_DOMAIN,
            'choice_translation_domain' => EMSCoreBundle::TRANS_FORM_DOMAIN,
        ]);
    }
}
