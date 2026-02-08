<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Webhook;

readonly class Webhook
{
    public const string VALIDATE_WEBHOOK_SUBSCRIBER = 'validate_webhook_subscription';
    public const string WEBHOOK_TEST = 'webhook_test';
    public const string WEBHOOK_PING = 'webhook_ping';
    /** @var string[] */
    public const array MUST_BE_SUPPORTED = [
        self::VALIDATE_WEBHOOK_SUBSCRIBER,
        self::WEBHOOK_TEST,
        self::WEBHOOK_PING,
    ];

    /**
     * @param mixed[] $data
     */
    public function __construct(public string $eventName, public array $data)
    {
    }

    public function getName(): string
    {
        return $this->eventName;
    }
}
