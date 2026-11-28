<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use Symfony\Component\Form\FormTypeInterface;

interface FieldInterface
{
    public function getHtmlClass(): string;

    /**
     * @return class-string<FormTypeInterface<mixed>>
     */
    public function getFieldClass(): string;

    /** @return mixed[] */
    public function getOptions(): array;
}
