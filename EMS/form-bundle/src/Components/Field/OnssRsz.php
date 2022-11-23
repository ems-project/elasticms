<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BelgiumOnssRszNumber;

class OnssRsz extends AbstractForgivingNumberField
{
    public function getHtmlClass(): string
    {
        return 'onss-rsz';
    }

    public function getTransformerClasses(): array
    {
        return [BelgiumOnssRszNumber::class];
    }
}
