<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

class BelgiumCompanyNumber
{
    private readonly NumberValue $number;

    public function __construct(string $companyNumber)
    {
        $this->number = new NumberValue($companyNumber);

        if (!$this->validate()) {
            throw new \Exception(\sprintf('invalid company registration number data: %s', $companyNumber));
        }
    }

    public function validate(): bool
    {
        $numberOfDigits = \strlen($this->number->getDigits());
        $firstDigit = \substr($this->number->getDigits(), 0, 1);

        return (10 === $numberOfDigits) && (('0' === $firstDigit) || ('1' === $firstDigit));
    }

    public function transform(): string
    {
        return $this->number->getDigits();
    }
}
