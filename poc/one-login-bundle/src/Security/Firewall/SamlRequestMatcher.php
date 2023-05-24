<?php

declare(strict_types=1);

namespace EMS\OneLoginBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SamlRequestMatcher implements RequestMatcherInterface
{
    private const REGEX = '/^\/(nl|fr).*/';


    public function matches(Request $request): bool
    {
        return true;

        if ($request->getPathInfo() === '/saml/acs' or $request->getPathInfo() === '/saml/logout') {
            return true;
        }

        return (bool) \preg_match(self::REGEX, $request->getPathInfo());
    }
}