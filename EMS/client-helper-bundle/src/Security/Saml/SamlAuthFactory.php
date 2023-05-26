<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Saml;

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\HttpUtils;

class SamlAuthFactory
{
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
            'debug' => true,
            'baseurl' => $this->httpUtils->generateUri($request, SamlConfig::PATH_SAML),
            'sp' => [
                'entityId' => $this->samlConfig->spEntityId(),
                'assertionConsumerService' => [
                    'url' => $this->httpUtils->generateUri($request, SamlConfig::PATH_SAML_ACS),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                ],
                'x509cert' => $this->samlConfig->spPublicKey(),
                'privateKey' => $this->samlConfig->spPrivateKey(),
            ],
            'idp' => [
                'entityId' => $this->samlConfig->idpEntityId(),
                'singleSignOnService' => [
                    'url' => $this->samlConfig->idpSSO(),
                    'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                ],
                'x509cert' => $this->samlConfig->idpPublicKey(),
            ],
            'security' => \array_merge_recursive(self::DEFAULT_SECURITY, $this->samlConfig->security()),
        ]);
    }
}
