<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Webhook;

readonly class Webhook
{
    /**
     * @param mixed[] $data
     */
    public function __construct(public string $eventName, public array $data)
    {
    }
}
