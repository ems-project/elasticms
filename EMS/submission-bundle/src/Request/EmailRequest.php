<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

final class EmailRequest
{
    /** @var string */
    private $from;
    private readonly string $subject;
    private string $body = '';
    private string $contentType = '';
    /** @var array<array<mixed>> */
    private readonly array $attachments;
    private ?string $replyTo;

    /**
     * @param array<string, mixed> $message
     */
    public function __construct(private readonly string $endpoint, array $message)
    {
        if (!isset($message['from'])) {
            throw new \Exception('From email address not defined.');
        }

        $this->from = $message['from'];
        $this->subject = $message['subject'] ?? 'Email submission';
        $this->body = $message['body'] ?? '';
        $this->attachments = $message['attachments'] ?? [];
        $this->contentType = $message['content-type'] ?? 'text/plain';
        $this->replyTo = $message['reply-to'] ?? null;
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

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }
}
