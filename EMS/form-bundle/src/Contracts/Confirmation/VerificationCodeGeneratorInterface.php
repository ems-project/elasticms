<?php

declare(strict_types=1);

namespace EMS\FormBundle\Contracts\Confirmation;

use EMS\FormBundle\Service\Endpoint\EndpointInterface;

interface VerificationCodeGeneratorInterface
{
    public function generate(EndpointInterface $endpoint, string $confirmValue): string;

    public function getVerificationCode(EndpointInterface $endpoint, string $confirmValue): ?string;
}
