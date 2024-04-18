<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller\Security\Sso;

use EMS\ClientHelperBundle\Security\Sso\OAuth2\OAuth2Service;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuth2Controller
{
    public function __construct(
        private readonly OAuth2Service $oAuth2Service
    ) {
    }

    public function login(): RedirectResponse
    {
        return new RedirectResponse($this->oAuth2Service->getProvider()->getAuthorizationUrl());
    }
}
