<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File as FileConstraints;

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
        $fileConstraints = \array_filter($constraints, fn (Constraint $c) => $c instanceof FileConstraints);

        if ([] === $fileConstraints) {
            return $constraints;
        }

        $topLevel = \array_filter($constraints, fn (Constraint $c) => !\in_array($c, $fileConstraints, true));

        return \array_merge($topLevel, [new All(\array_values($fileConstraints))]);
    }
}
