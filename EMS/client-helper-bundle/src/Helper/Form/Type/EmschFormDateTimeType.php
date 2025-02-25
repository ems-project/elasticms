<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Form\Type;

use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form\FormInterface;
use EMS\Helpers\Standard\DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class EmschFormDateTimeType extends AbstractType
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
            function ($value) use ($dataFormat): ?\DateTimeInterface {
                return \is_string($value) ? DateTime::createFromFormat($value, $dataFormat) : null;
            },
            function ($value) use ($dataFormat) {
                return $value instanceof \DateTimeInterface ? $value->format($dataFormat) : null;
            }
        ));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => true,
            'data_format' => \DateTimeInterface::ATOM,
        ]);
    }

    #[\Override]
    public function getParent(): string
    {
        return DateTimeType::class;
    }
}
