<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\DataTransformers\ForgivingNumberDataTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractForgivingNumberField extends AbstractField
{
    #[\Override]
    public function getFieldClass(): string
    {
        return TextType::class;
    }

    /** @return string[] */
    public function getTransformerClasses(): array
    {
        return [];
    }

    /**
     * @return DataTransformerInterface<mixed, mixed>
     */
    public function getDataTransformer(): DataTransformerInterface
    {
        return new ForgivingNumberDataTransformer($this->getTransformerClasses());
    }
}
