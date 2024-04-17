<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Sso\Saml;

class SamlConfig
{
    public const PATH_SAML = '/saml';
    public const ROUTE_METADATA = 'emsch_saml_metadata';
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
    public function __construct(private readonly array $config)
    {
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? false;
    }

    public function property(SamlProperty $property): string
    {
        return $this->config[$property->value];
    }

    /**
     * @return array<string, mixed>
     */
    public function security(): array
    {
        return \array_merge_recursive(self::DEFAULT_SECURITY, $this->config[SamlProperty::SECURITY->value]);
    }
}
