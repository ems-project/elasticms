<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

class BelgiumOnssRszNumber
{
    private readonly NumberValue $number;

    public function __construct(string $nsso)
    {
        $this->number = new NumberValue($nsso);

        if (!$this->validate()) {
            throw new \Exception(\sprintf('invalid NSSO data: %s', $nsso));
        }
    }

    public function validate(): bool
    {
        $numberOfDigits = \strlen($this->number->getDigits());

        return ($numberOfDigits >= 9) and ($numberOfDigits <= 10);
    }

    public function transform(): string
    {
        return $this->number->getDigits();
    }
}
