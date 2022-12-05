<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Confirmation;

use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\FormBundle\Contracts\Confirmation\VerificationCodeGeneratorInterface;
use EMS\FormBundle\Service\Endpoint\EndpointInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class VerificationCodeGenerator implements VerificationCodeGeneratorInterface
{
    public function __construct(private readonly CoreApiInterface $coreApi, private readonly SessionInterface $session)
    {
    }

    public function getVerificationCode(EndpointInterface $endpoint, string $confirmValue): ?string
    {
        if ($endpoint->saveInSession()) {
            $verificationCode = $this->session->get($this->getSessionKey($confirmValue), false);
        } else {
            $verificationCode = $this->coreApi->form()->getVerification($confirmValue);
        }

        return \is_string($verificationCode) ? $verificationCode : null;
    }

    public function generate(EndpointInterface $endpoint, string $confirmValue): string
    {
        if (!$endpoint->saveInSession()) {
            return $this->coreApi->form()->createVerification($confirmValue);
        }

        $verificationCode = $this->session->get($this->getSessionKey($confirmValue));

        if (null === $verificationCode) {
            $verificationCode = \sprintf('%d%05d', \random_int(1, 9), \random_int(0, 99999));
            $this->session->set($this->getSessionKey($confirmValue), $verificationCode);
        }

        return $verificationCode;
    }

    private function getSessionKey(string $value): string
    {
        return \sprintf('EMS_CC_[%s]', $value);
    }
}
