<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Validation;

use Symfony\Component\Validator\Constraint;

interface ValidationInterface
{
    public function getHtml5AttributeName(): string;

    public function getConstraint(): Constraint;

    /** @return array<string, mixed> */
    public function getHtml5Attribute(): array;
}
