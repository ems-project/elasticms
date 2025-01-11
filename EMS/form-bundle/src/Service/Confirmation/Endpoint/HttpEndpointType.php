<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Confirmation\Endpoint;

use EMS\FormBundle\Contracts\Confirmation\VerificationCodeGeneratorInterface;
use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\Service\Endpoint\EndpointInterface;
use EMS\FormBundle\Service\Endpoint\EndpointTypeInterface;
use EMS\Helpers\Standard\Json;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HttpEndpointType extends ConfirmationEndpointType implements EndpointTypeInterface
{
    public const string NAME = 'http';

    public function __construct(private readonly HttpClientInterface $httpClient, private readonly TranslatorInterface $translator, private readonly VerificationCodeGeneratorInterface $verificationCodeGenerator)
    {
    }

    #[\Override]
    public function canExecute(EndpointInterface $endpoint): bool
    {
        return self::NAME === $endpoint->getType();
    }

    #[\Override]
    public function getVerificationCode(EndpointInterface $endpoint, string $confirmValue): ?string
    {
        return $this->verificationCodeGenerator->getVerificationCode($endpoint, $confirmValue);
    }

    #[\Override]
    public function confirm(EndpointInterface $endpoint, FormConfig $formConfig, string $confirmValue): bool
    {
        $verificationCode = $this->verificationCodeGenerator->generate($endpoint, $confirmValue);
        $replaceBody = ['%value%' => $confirmValue, '%verification_code%' => $verificationCode];

        if (null !== $messageTranslationKey = $endpoint->getMessageTranslationKey()) {
            $messageTranslation = $this->translator->trans(
                $messageTranslationKey,
                $replaceBody,
                $formConfig->getTranslationDomain()
            );

            $replaceBody = \array_merge(['%message_translation%' => $messageTranslation], $replaceBody);
        }

        $response = $this->request($endpoint, $replaceBody);

        $result = Json::decode($response->getContent());

        if (!isset($result['ResultCode']) || 0 !== $result['ResultCode']) {
            throw new \Exception(\sprintf('Invalid endpoint response %s', $response->getContent()));
        }

        return true;
    }

    /**
     * @param array<string, string> $replaceBody
     */
    public function request(EndpointInterface $endpoint, array $replaceBody, int $timeout = 20): ResponseInterface
    {
        $httpRequest = $endpoint->getHttpRequest();

        return $this->httpClient->request($httpRequest->getMethod(), $httpRequest->getUrl(), [
            'headers' => $httpRequest->getHeaders(),
            'body' => $httpRequest->createBody($replaceBody),
            'max_duration' => $timeout,
        ]);
    }
}
