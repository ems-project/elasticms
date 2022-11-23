<?php

namespace EMS\FormBundle\FormConfig;

class SubmissionConfig
{
    private string $class;
    private string $endpoint;
    private string $message;

    public function __construct(string $class, string $endpoint, string $message)
    {
        $this->class = $class;
        $this->endpoint = $endpoint;
        $this->message = $message;
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
