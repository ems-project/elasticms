<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\Saml;

enum SamlProperty: string
{
    case SP_ENTITY_ID = 'sp_entity_id';
    case SP_PUBLIC_KEY = 'sp_public_key';
    case SP_PRIVATE_KEY = 'sp_private_key';

    case IDP_ENTITY_ID = 'idp_entity_id';
    case IDP_PUBLIC_KEY = 'idp_public_key';
    case IDP_SSO = 'idp_sso';

    case SECURITY = 'security';
}
