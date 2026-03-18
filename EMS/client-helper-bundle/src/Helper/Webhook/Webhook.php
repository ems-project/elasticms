<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Webhook;

readonly class Webhook
{
    public const string VALIDATE_WEBHOOK_SUBSCRIBER = 'validate_webhook_subscription';

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
