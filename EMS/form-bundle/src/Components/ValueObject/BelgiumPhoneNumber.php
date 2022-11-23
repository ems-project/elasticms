<?php

namespace EMS\FormBundle\Components\ValueObject;

class BelgiumPhoneNumber
{
    /** @var NumberValue */
    private $number;

    public const LOCAL = 'local';
    public const INTERNATIONAL_PLUS = 'plus';
    public const INTERNATIONAL_ZEROS = 'zeros';

    public function __construct(string $phone)
    {
        $this->number = new NumberValue($phone);

        if (!$this->validate()) {
            throw new \Exception(\sprintf('invalid phone data: %s', $phone));
        }
    }

    public function validate(): bool
    {
        $numberType = $this->getNumberType();

        if ($this->validateNumberOfDigit($numberType) && $this->validateCountryCode($numberType) && $this->validateLongDistanceCode($numberType)) {
            return true;
        }

        return false;
    }

    private function validateNumberOfDigit(string $numberType): bool
    {
        $numberOfDigits = \strlen($this->number->getDigits());

        if (self::INTERNATIONAL_ZEROS === $numberType) {
            return (13 === $numberOfDigits) || (12 === $numberOfDigits);
        }

        if (self::INTERNATIONAL_PLUS === $numberType) {
            return (11 === $numberOfDigits) || (10 === $numberOfDigits);
        }

        if (self::LOCAL === $numberType) {
            return (10 === $numberOfDigits) || (9 === $numberOfDigits);
        }

        return false;
    }

    private function validateCountryCode(string $numberType): bool
    {
        if (self::INTERNATIONAL_ZEROS === $numberType) {
            return 2 === \strpos($this->transform(), '32');
        }

        if (self::INTERNATIONAL_PLUS === $numberType) {
            return 1 === \strpos($this->transform(), '32');
        }

        if (self::LOCAL === $numberType) {
            return true;
        }

        return false;
    }

    private function validateLongDistanceCode(string $numberType): bool
    {
        if (self::INTERNATIONAL_ZEROS === $numberType) {
            return 4 !== \strpos($this->transform(), '0', 2);
        }

        if (self::INTERNATIONAL_PLUS === $numberType) {
            return 3 !== \strpos($this->transform(), '0');
        }

        if (self::LOCAL === $numberType) {
            return 0 === \strpos($this->transform(), '0');
        }

        return false;
    }

    private function getNumberType(): string
    {
        if (0 === \strpos($this->transform(), '+')) {
            return self::INTERNATIONAL_PLUS;
        }

        if (0 === \strpos($this->transform(), '00')) {
            return self::INTERNATIONAL_ZEROS;
        }

        return self::LOCAL;
    }

    public function transform(): string
    {
        if (0 === \strpos($this->number->getInput(), '+')) {
            return '+'.$this->number->getDigits();
        }

        return $this->number->getDigits();
    }
}
