<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Hashcash;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

final readonly class HashcashHelper
{
    public function __construct(private CsrfTokenManager $csrfTokenManager)
    {
    }

    public function validateHashcash(Request $request, string $csrfId, int $hashcashLevel = 4, string $hashAlgo = 'sha256'): void
    {
        $hashcash = $request->headers->get('X-Hashcash');
        if (null === $hashcash) {
            throw new AccessDeniedHttpException('Unrecognized user');
        }

        $token = new Token($hashcash);

        if ($token->getLevel() < $hashcashLevel) {
            throw new AccessDeniedHttpException('Insufficient security level by definition');
        }

        if (!\preg_match(\sprintf('/^0{%d}/', $hashcashLevel), \hash($hashAlgo, $hashcash))) {
            throw new AccessDeniedHttpException('Insufficient security level');
        }

        $csrfToken = new CsrfToken($csrfId, $token->getCsrf());
        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new AccessDeniedHttpException('Unrecognized key');
        }
    }
}
