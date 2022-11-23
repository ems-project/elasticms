<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Confirmation\Endpoint;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\Service\Endpoint\EndpointInterface;
use EMS\FormBundle\Service\Endpoint\EndpointTypeInterface;

abstract class ConfirmationEndpointType implements EndpointTypeInterface
{
    /**
     * Called for sending a confirmation code.
     *
     * @return mixed
     */
    abstract public function confirm(EndpointInterface $endpoint, FormConfig $formConfig, string $confirmValue);

    /**
     * Get the verification code, for validating the form.
     */
    abstract public function getVerificationCode(EndpointInterface $endpoint, string $confirmValue): ?string;
}
