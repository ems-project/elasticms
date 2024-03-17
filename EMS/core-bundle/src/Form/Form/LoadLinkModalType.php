<?php

namespace EMS\CoreBundle\Form\Form;

use EMS\CoreBundle\Form\Field\ObjectPickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class LoadLinkModalType extends AbstractType
{
    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed>                       $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('href', TextType::class, [
                'required' => false,
            ])
            ->add('dataLink', ObjectPickerType::class, [
                'required' => false,
                'multiple' => false,
            ]);
    }
}
