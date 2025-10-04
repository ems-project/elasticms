<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form\Type;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;
use EMS\Helpers\Standard\DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class EmschDateType extends AbstractType
{
    /**
     * @param FormBuilderInterface<FormInterface> $builder
     * @param array<mixed>                        $options
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataFormat = $options['data_format'];

        $builder->addModelTransformer(new CallbackTransformer(
            fn ($value): ?\DateTimeInterface => \is_string($value) ? DateTime::createFromFormat($value, $dataFormat) : null,
            fn ($value) => $value instanceof \DateTimeInterface ? $value->format($dataFormat) : null
        ));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => true,
            'data_format' => 'Y/m/d',
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return DateType::class;
    }
}
