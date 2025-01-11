<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

final readonly class InternationalPhoneNumber
{
    private NumberValue $number;

    public function __construct(string $phone)
    {
        $this->number = new NumberValue($phone);

        if (!$this->validate()) {
            throw new \Exception(\sprintf('invalid phone data: %s', $phone));
        }
    }

    public function validate(): bool
    {
        if ($this->validateNumber()) {
            return true;
        }

        return false;
    }

    private function validateNumber(): bool
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneNumberObject = $phoneNumberUtil->parse($this->number->getInput(), null);
        } catch (\Exception) {
            return false;
        }

        return $phoneNumberUtil->isValidNumber($phoneNumberObject);
    }

    public function transform(): string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($this->number->getInput(), null);

        return $phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::E164);
    }
}
