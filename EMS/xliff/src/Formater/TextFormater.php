<?php

declare(strict_types=1);

namespace EMS\Xliff\Formater;

class TextFormater implements FormaterInterface
{
    public function format(string $input): string
    {
        return $input;
    }
}
