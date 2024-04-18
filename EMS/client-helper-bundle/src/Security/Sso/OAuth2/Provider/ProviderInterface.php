<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

interface ProviderInterface
{
    public function redirect(Request $request): RedirectResponse;

    public function getAccessToken(Request $request): AccessTokenInterface;

    public function getUsername(AccessTokenInterface $token): string;
}
