<?php

namespace EMS\FormBundle\Components\Field;

class DateWithPicker extends Date
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'date-with-picker';
    }
}
