<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\Saml;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\HttpUtils;

class SamlAuthFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly SamlConfig $samlConfig,
        private readonly HttpUtils $httpUtils
    ) {
    }

    public function create(): Auth
    {
        if (null === $request = $this->requestStack->getMainRequest()) {
            throw new \RuntimeException('No request');
        }

        Utils::setProxyVars($request->isFromTrustedProxy());

        return new Auth([
            'strict' => true,
            'debug' => false,
            'baseurl' => $this->httpUtils->generateUri($request, SamlConfig::PATH_SAML),
            'sp' => [
                'entityId' => $this->samlConfig->property(SamlProperty::SP_ENTITY_ID),
                'assertionConsumerService' => [
                    'url' => $this->httpUtils->generateUri($request, SamlConfig::ROUTE_ACS),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'x509cert' => $this->samlConfig->property(SamlProperty::SP_PUBLIC_KEY),
                'privateKey' => $this->samlConfig->property(SamlProperty::SP_PRIVATE_KEY),
            ],
            'idp' => [
                'entityId' => $this->samlConfig->property(SamlProperty::IDP_ENTITY_ID),
                'singleSignOnService' => [
                    'url' => $this->samlConfig->property(SamlProperty::IDP_SSO),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => $this->samlConfig->property(SamlProperty::IDP_PUBLIC_KEY),
            ],
            'security' => $this->samlConfig->security(),
        ]);
    }
}
