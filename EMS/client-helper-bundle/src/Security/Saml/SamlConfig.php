<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Security\Saml;

class SamlConfig
{
    public const PATH_SAML = '/saml';
    public const PATH_SAML_LOGIN = '/saml/login';
    public const PATH_SAML_METADATA = '/saml/metadata';
    public const PATH_SAML_ACS = '/saml/acs';

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

    public function spPublicKey(): string
    {
        return $this->config['sp']['public_key'];
    }

    public function spPrivateKey(): string
    {
        return $this->config['sp']['private_key'];
    }

    public function idpPublicKey(): string
    {
        return $this->config['idp']['public_key'];
    }

    public function spEntityId(): string
    {
        return $this->config['sp']['entity_id'];
    }

    public function idpEntityId(): string
    {
        return $this->config['idp']['entity_id'];
    }

    public function idpSSO(): string
    {
        return $this->config['idp']['sso'];
    }

    /**
     * @return array<string, mixed>
     */
    public function security(): array
    {
        return $this->config['security'] ?? [];
    }
}
