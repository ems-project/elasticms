<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Core\Form\FormManager;
use EMS\CoreBundle\Core\User\UserOptions;
use EMS\CoreBundle\Form\DataTransformer\DataFieldModelTransformer;
use EMS\CoreBundle\Form\DataTransformer\DataFieldViewTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserOptionsType extends AbstractType
{
    public function __construct(
        private readonly FormManager $formManager,
        protected FormRegistryInterface $formRegistry,
        private readonly ?string $customUserOptionsForm)
    {
    }

    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(UserOptions::SIMPLIFIED_UI, CheckboxType::class, [
                'required' => false,
                'label' => 'user.option.simplified_ui',
            ]);
        if (null !== $this->customUserOptionsForm) {
            $form = $this->formManager->getByName($this->customUserOptionsForm);
            $builder->add(UserOptions::CUSTOM_OPTIONS, $form->getFieldType()->getType(), [
                'metadata' => $form->getFieldType(),
                'label' => false,
            ]);

            $builder->get(UserOptions::CUSTOM_OPTIONS)
                ->addViewTransformer(new DataFieldViewTransformer($form->getFieldType(), $this->formRegistry))
                ->addModelTransformer(new DataFieldModelTransformer($form->getFieldType(), $this->formRegistry));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'emsco-user']);
    }
}
