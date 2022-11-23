<?php

namespace EMS\FormBundle\Components\ValueObject;

class NumberValue
{
    /** @var string */
    private $input;
    /** @var string */
    private $digits;

    public function __construct(string $number)
    {
        $this->input = $number;
        $this->digits = $this->filterNumbers($number);
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getDigits(): string
    {
        return $this->digits;
    }

    private function filterNumbers(string $number): string
    {
        \preg_match_all('!\d+!', $number, $matches);
        $digits = '';
        foreach ($matches[0] as $digit) {
            $digits .= $digit;
        }

        return $digits;
    }

    public function transform(): string
    {
        return $this->getDigits();
    }
}
