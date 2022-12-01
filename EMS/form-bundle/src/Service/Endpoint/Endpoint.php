<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Endpoint;

use EMS\FormBundle\Service\Confirmation\Endpoint\HttpEndpointType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Endpoint implements EndpointInterface
{
    private readonly string $fieldName;
    private readonly ?string $messageTranslationKey;
    private readonly HttpRequest $httpRequest;
    private readonly bool $saveSession;
    private readonly string $type;
    /** @var array<mixed> */
    private readonly array $options;

    /** @param array<string, mixed> $config */
    public function __construct(array $config)
    {
        $config = $this->getOptionsResolver()->resolve($config);

        $this->fieldName = $config['field_name'];
        $this->httpRequest = new HttpRequest($config['http_request']);
        $this->messageTranslationKey = $config['message_translation_key'] ?? null;
        $this->saveSession = $config['save_session'] ?? true;
        $this->type = $config['type'];
        $this->options = $config['options'];
    }

    /**
     * @return array<mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getHttpRequest(): HttpRequest
    {
        return $this->httpRequest;
    }

    public function getMessageTranslationKey(): ?string
    {
        return $this->messageTranslationKey;
    }

    public function saveInSession(): bool
    {
        return $this->saveSession;
    }

    private function getOptionsResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver
            ->setRequired(['field_name'])
            ->setDefaults([
                'message_translation_key' => null,
                'http_request' => [],
                'type' => HttpEndpointType::NAME,
                'save_session' => true,
                'options' => [],
            ]);

        return $optionsResolver;
    }
}
