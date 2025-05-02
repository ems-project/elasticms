<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Mercure;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    public function __construct(
        private readonly HubInterface $mercureHub,
        private readonly string $mercurePublicUrl,
        private readonly string $subscriberJWT,
        private readonly string $userUrl,
    )
    {
    }
    
    /** @return array{ 'token': string, 'url': string } */
    public function generateToken(string $expiresAt): array
    {
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->subscriberJWT)
        );

        $now = new \DateTimeImmutable('now');
        $token = $config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify($expiresAt))
            ->withClaim('mercure', ['subscribe' => ['http://localhost:8881/demo']])
            ->getToken($config->signer(), $config->signingKey());

        return [
            'token' => $token->toString(),
            'url' => $this->mercurePublicUrl,
        ];
    }

    public function publish(string $message): void
    {
        $update = new Update(
            'http://localhost:8881/demo', // topic
            json_encode([
                'message' => $message
            ])
        );

        $this->mercureHub->publish($update);
    }
}