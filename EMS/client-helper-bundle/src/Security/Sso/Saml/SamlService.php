<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\Saml;

use EMS\ClientHelperBundle\Controller\Security\Sso\SamlController;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
use Symfony\Component\Security\Http\HttpUtils;

class SamlService
{
    private const PATH_SAML = '/saml';
    private const ROUTE_METADATA = 'emsch_saml_metadata';
    public const ROUTE_LOGIN = 'emsch_saml_login';
    public const ROUTE_ACS = 'emsch_saml_acs';

    private const DEFAULT_SECURITY = [
        'nameIdEncrypted' => false,
        'authnRequestsSigned' => false,
        'logoutRequestSigned' => false,
        'logoutResponseSigned' => false,
        'signMetadata' => false,
        'wantMessagesSigned' => false,
        'wantAssertionsEncrypted' => false,
        'wantAssertionsSigned' => false,
        'wantNameId' => true,
        'wantNameIdEncrypted' => false,
        'requestedAuthnContext' => false,
        'wantXMLValidation' => true,
        'relaxDestinationValidation' => false,
        'destinationStrictlyMatches' => false,
        'allowRepeatAttributeName' => true,
        'rejectUnsolicitedResponsesWithInResponseTo' => false,
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
        'encryption_algorithm' => 'http://www.w3.org/2009/xmlenc11#aes128-gcm',
        'lowercaseUrlencoding' => false,
    ];

    /**
     * @param array<mixed> $config
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly HttpUtils $httpUtils,
        private readonly array $config,
    ) {
    }

    public function auth(): Auth
    {
        if (null === $request = $this->requestStack->getMainRequest()) {
            throw new \RuntimeException('No request');
        }

        Utils::setProxyVars($request->isFromTrustedProxy());

        return new Auth([
            'strict' => true,
            'debug' => false,
            'baseurl' => $this->httpUtils->generateUri($request, self::PATH_SAML),
            'sp' => [
                'entityId' => $this->property(SamlProperty::SP_ENTITY_ID),
                'assertionConsumerService' => [
                    'url' => $this->httpUtils->generateUri($request, self::ROUTE_ACS),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'x509cert' => $this->property(SamlProperty::SP_PUBLIC_KEY),
                'privateKey' => $this->property(SamlProperty::SP_PRIVATE_KEY),
            ],
            'idp' => [
                'entityId' => $this->property(SamlProperty::IDP_ENTITY_ID),
                'singleSignOnService' => [
                    'url' => $this->property(SamlProperty::IDP_SSO),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => $this->property(SamlProperty::IDP_PUBLIC_KEY),
            ],
            'security' => $this->getSecurity(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->httpUtils->createRedirectResponse($request, self::ROUTE_LOGIN);
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function registerRoutes(CollectionConfigurator $routes): void
    {
        $routes
            ->add(self::ROUTE_METADATA, '/saml/metadata')
                ->controller([SamlController::class, 'metadata'])
                ->methods(['GET'])
            ->add(self::ROUTE_LOGIN, '/saml/login')
                ->controller([SamlController::class, 'login'])
                ->methods(['GET'])
            ->add(self::ROUTE_ACS, '/saml/acs')
                ->controller([SamlController::class, 'acs'])
                ->methods(['POST'])
        ;
    }

    private function property(SamlProperty $property): string
    {
        return $this->config[$property->value];
    }

    /**
     * @return array<string, mixed>
     */
    private function getSecurity(): array
    {
        return \array_merge_recursive(self::DEFAULT_SECURITY, $this->config[SamlProperty::SECURITY->value]);
    }
}
