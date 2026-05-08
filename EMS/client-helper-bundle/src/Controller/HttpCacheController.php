<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Webhook\Webhook;
use EMS\ClientHelperBundle\Helper\Webhook\WebhookHelper;
use EMS\CommonBundle\Common\HttpCache\HttpCacheManager;
use EMS\Helpers\Standard\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class HttpCacheController extends AbstractController
{
    public function __construct(
        private readonly HttpCacheManager $httpCacheManager,
        private readonly WebhookHelper $webhookHelper,
    ) {
    }

    public function adminWebhook(): Response
    {
        $webhook = $this->webhookHelper->getWebhook();
        if (\str_starts_with($webhook->eventName, 'content.published.') || \in_array($webhook->eventName, ['content.finalize', 'content.unpublish', 'content.delete'], true)) {
            $ouuid = Type::string($webhook->data['ouuid'] ?? null);
            $this->httpCacheManager->purgeByTags($ouuid);
        } elseif (\str_starts_with($webhook->eventName, 'environment.new_index.') || \str_starts_with($webhook->eventName, 'alias.update.')) {
            $this->httpCacheManager->purgeAll();
        } elseif (!\in_array($webhook->eventName, Webhook::MUST_BE_SUPPORTED, true)) {
            throw new \RuntimeException(\sprintf('event type %s not supported', $webhook->eventName));
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
