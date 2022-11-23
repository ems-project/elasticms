<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

final class EmailRequest
{
    /** @var string */
    private $endpoint;
    /** @var string */
    private $from;
    /** @var string */
    private $subject;
    /** @var string */
    private $body = '';
    private string $contentType = '';
    /** @var array<array<mixed>> */
    private $attachments;

    /**
     * @param array<string, mixed> $message
     */
    public function __construct(string $endpoint, array $message)
    {
        $this->endpoint = $endpoint;

        if (!isset($message['from'])) {
            throw new \Exception('From email address not defined.');
        }

        $this->from = $message['from'];
        $this->subject = $message['subject'] ?? 'Email submission';
        $this->body = $message['body'] ?? '';
        $this->attachments = $message['attachments'] ?? [];
        $this->contentType = $message['content-type'] ?? 'text/plain';
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return array<array<mixed>>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
