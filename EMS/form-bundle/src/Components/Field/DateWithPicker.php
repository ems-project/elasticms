<?php

namespace EMS\FormBundle\Components\Field;

class DateWithPicker extends Date
{
    public function getHtmlClass(): string
    {
        return 'date-with-picker';
    }
}
