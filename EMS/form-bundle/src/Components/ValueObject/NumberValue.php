<?php

declare(strict_types=1);

namespace EMS\FormBundle\Components\ValueObject;

class NumberValue
{
    private readonly string $digits;

    public function __construct(private readonly string $input)
    {
        $this->digits = $this->filterNumbers($input);
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
