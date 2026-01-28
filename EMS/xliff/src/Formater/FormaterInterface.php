<?php

declare(strict_types=1);

namespace EMS\Xliff\Formater;

interface FormaterInterface
{
    public function format(string $input): string;
}
