<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\Field;

use EMS\FormBundle\Components\ValueObject\BisNumber;
use EMS\FormBundle\Components\ValueObject\RrNumber;

class NissInsz extends AbstractForgivingNumberField
{
    #[\Override]
    public function getHtmlClass(): string
    {
        return 'niss-insz';
    }

    #[\Override]
    public function getTransformerClasses(): array
    {
        return [BisNumber::class, RrNumber::class];
    }
}
