<?php

declare(strict_types=1);

namespace EMS\FormBundle\FormConfig;

class SubmissionConfig implements \JsonSerializable
{
    public function __construct(
        private readonly string $class,
        private readonly string $endpoint,
        private readonly string $message
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return \get_object_vars($this);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
