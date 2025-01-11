<?php

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BelgiumOnssRszNumber;

class OnssRsz extends AbstractForgivingNumberField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'onss-rsz';
    }

    #[\Override]
    public function getTransformerClasses(): array
    {
        return [BelgiumOnssRszNumber::class];
    }
}
