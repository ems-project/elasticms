<?php

namespace EMS\FormBundle\FormConfig;

class SubmissionConfig
{
    public function __construct(private readonly string $class, private readonly string $endpoint, private readonly string $message)
    {
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
