<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Endpoint;

interface EndpointInterface
{
    public function getType(): string;

    public function getFieldName(): string;

    public function getHttpRequest(): HttpRequest;

    public function saveInSession(): bool;

    public function getMessageTranslationKey(): ?string;

    /**
     * @return array<mixed>
     */
    public function getOptions(): array;
}
