<?php

namespace EMS\FormBundle\Components\Validation;

class MaxLengthCounter extends MaxLength
{
    public function getHtml5Attribute(): array
    {
        return [
            'data-maxlength' => $this->value,
            'class' => ['counter'],
        ];
    }
}
