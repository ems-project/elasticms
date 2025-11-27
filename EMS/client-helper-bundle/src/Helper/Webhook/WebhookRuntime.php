<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Webhook;

class WebhookRuntime
{
    public function __construct(private readonly WebhookHelper $webhookHelper)
    {
    }

    public function getWebhook(): Webhook
    {
        return $this->webhookHelper->getWebhook();
    }
}
