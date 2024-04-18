<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\OAuth2;

enum OAuth2Property: string
{
    case PROVIDER = 'provider';
    case AUTH_SERVER = 'auth_server';
    case REALM = 'realm';
    case CLIENT_ID = 'client_id';
    case CLIENT_SECRET = 'client_secret';
    case REDIRECT_URI = 'redirect_uri';
    case VERSION = 'version';
    case ENCRYPTION_ALGORITHM = 'encryption_algorithm';
    case ENCRYPTION_KEY = 'encryption_key';
}
