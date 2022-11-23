<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\CoreApi\Endpoint\Form;

interface FormInterface
{
    public function createVerification(string $value): string;

    public function getVerification(string $value): string;
}
