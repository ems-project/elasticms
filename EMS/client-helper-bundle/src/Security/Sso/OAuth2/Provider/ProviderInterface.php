<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2\Provider;

use League\OAuth2\Client\Token\AccessTokenInterface;

interface ProviderInterface
{
    public function getAuthorizationUrl(): string;

    public function getAccessToken(string $code): AccessTokenInterface;

    public function getUsername(AccessTokenInterface $token): string;
}
