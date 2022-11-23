<?php

namespace EMS\FormBundle\Components\DataTransformers;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<mixed, mixed>
 */
class ForgivingNumberDataTransformer implements DataTransformerInterface
{
    /** @var string[] */
    private array $transformerClasses;

    /**
     * @param string[] $transformerClasses
     */
    public function __construct(array $transformerClasses)
    {
        $this->transformerClasses = $transformerClasses;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        if (null === $value) {
            return;
        }

        foreach ($this->transformerClasses as $class) {
            try {
                $validation = new $class($value);

                if (\method_exists($validation, 'transform')) {
                    return $validation->transform();
                }
            } catch (\Exception $exception) {
                continue;
            }
        }
        throw new TransformationFailedException(\sprintf('Is not a valid number "%s"', $value));
    }
}
