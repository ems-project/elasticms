<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\Helpers\Standard\Json;

final class ServiceNowRequest
{
    private readonly string $host;
    private readonly string $table;
    private readonly string $attachmentTable;
    private readonly string $bodyEndpoint;
    private readonly string $attachmentEndpoint;
    private readonly string $username;
    private readonly string $password;
    private string $body = '';
    /** @var array<array<mixed>> */
    private array $attachments = [];

    /**
     * @param array<string, string> $endpoint
     * @param array<string, mixed>  $message
     */
    public function __construct(array $endpoint, array $message)
    {
        $this->host = $endpoint['host'];
        $this->table = $endpoint['table'];
        $this->attachmentTable = $endpoint['attachmentTable'] ?? $endpoint['table'];
        $this->username = $endpoint['username'];
        $this->password = $endpoint['password'];
        $this->bodyEndpoint = $endpoint['bodyEndpoint'] ?? '/api/now/table';
        $this->attachmentEndpoint = $endpoint['attachmentEndpoint'] ?? '/api/now/attachment/file';

        if (!empty($message['body'])) {
            $body = Json::encode($message['body']);
            $this->body = (!empty($body)) ? $body : '';
        }

        if (!empty($message['attachments'])) {
            $this->attachments = $message['attachments'];
        }
    }

    public function getBodyEndpoint(): string
    {
        return $this->host.$this->bodyEndpoint.'/'.$this->table;
    }

    public function getAttachmentEndpoint(): string
    {
        return $this->host.$this->attachmentEndpoint;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getAttachmentTable(): string
    {
        return $this->attachmentTable;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<array<mixed>>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getBasicAuth(): string
    {
        $credentials = \base64_encode(\sprintf('%s:%s', $this->getUsername(), $this->getPassword()));

        return \sprintf('Basic %s', $credentials);
    }
}
