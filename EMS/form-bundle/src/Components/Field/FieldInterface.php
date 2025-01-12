<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

interface FieldInterface
{
    public function getHtmlClass(): string;

    public function getFieldClass(): string;

    /** @return mixed[] */
    public function getOptions(): array;
}
