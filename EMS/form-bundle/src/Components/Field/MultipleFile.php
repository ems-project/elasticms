<?php

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;

class MultipleFile extends File
{
    #[\Override]
    public function getOptions(): array
    {
        $options = parent::getOptions();
        $options['multiple'] = true;

        return $options;
    }

    #[\Override]
    protected function getValidationConstraints(): array
    {
        $constraints = parent::getValidationConstraints();
        $countConstraints = \array_filter($constraints, fn (Constraint $constraint) => $constraint instanceof Count);

        if ($countConstraints == $constraints) {
            return $constraints;
        }

        $otherConstraints = \array_filter($constraints, fn (Constraint $constraint) => !$constraint instanceof Count);

        return \array_merge($countConstraints, [new All(['constraints' => $otherConstraints])]);
    }
}
