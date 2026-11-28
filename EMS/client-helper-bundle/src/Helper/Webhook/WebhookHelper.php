<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Webhook;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Security\Http\AccessToken\Oidc\Exception\InvalidSignatureException;

class WebhookHelper
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Cache $cacheManager,
    ) {
    }

    public function getWebhook(): Webhook
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new \RuntimeException('No request available');
        }

        $this->validateWebHookCall($request);
        $event = Json::decode(Type::string($request->getContent()));
        $eventName = Type::string($event['event'] ?? null);
        $data = Type::array($event['data'] ?? null);

        return new Webhook($eventName, $data);
    }

    private function validateWebHookCall(Request $request): void
    {
        $signature = Type::string($request->headers->get(Headers::X_WEBHOOK_SIGNATURE));
        $subscriptionId = Type::string($request->headers->get(Headers::X_WEBHOOK_SUBSCRIPTION_ID));
        $secret = $this->cacheManager->getItem(\sprintf('webhook_secret_%s', $subscriptionId));
        if (!$secret->isHit()) {
            throw new GoneHttpException(\sprintf('Unknown webhook subscription %s', $subscriptionId));
        }
        $hash = \hash_hmac('sha256', Type::string($request->getContent()), Type::string($secret->get()));
        if ($hash !== $signature) {
            throw new InvalidSignatureException();
        }
    }
}
