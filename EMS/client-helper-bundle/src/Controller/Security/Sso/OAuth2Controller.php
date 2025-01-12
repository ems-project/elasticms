<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\Security\Sso;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class OAuth2Controller
{
    public function __construct(
        private readonly OAuth2Service $oAuth2Service,
    ) {
    }

    public function login(Request $request): RedirectResponse
    {
        if ($request->getSession()->has(SecurityRequestAttributes::AUTHENTICATION_ERROR)) {
            $error = $request->getSession()->get(SecurityRequestAttributes::AUTHENTICATION_ERROR);
            throw new AuthenticationException($error->getMessage(), $error->getCode(), $error);
        }

        return $this->oAuth2Service->getProvider()->redirect($request);
    }

    public function redirect(): RedirectResponse
    {
        throw new \RuntimeException('invalid redirect');
    }
}
