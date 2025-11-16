<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\HttpCache\HttpCacheManager;
use EMS\Helpers\Html\Headers;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Security\Http\AccessToken\Oidc\Exception\InvalidSignatureException;

final class HttpCacheController extends AbstractController
{
    public function __construct(
        private HttpCacheManager $httpCacheManager,
        private Cache $cacheManager,
    ) {
    }

    public function adminWebhook(Request $request): Response
    {
        $this->validateWebHookCall($request);
        $event = Json::decode(Type::string($request->getContent()));
        $eventName = $event['event'] ?? null;
        $data = $event['data'] ?? null;
        if (!\is_string($eventName)) {
            throw new \RuntimeException('event name not provided');
        }
        if (!\is_array($data)) {
            throw new \RuntimeException('event data not provided');
        }

        if (\str_starts_with($eventName, 'content.published.') || 'content.finalize' == $eventName) {
            $ouuid = Type::string($data['ouuid']);
            $this->httpCacheManager->purgeByTags($ouuid);
        } else {
            throw new \RuntimeException('event type not supported');
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }

    private function validateWebHookCall(Request $request): void
    {
        $signature = Type::string($request->headers->get(Headers::X_WEBHOOK_SIGNATURE));
        $subscriptionId = Type::string($request->headers->get(Headers::X_WEBHOOK_SUBSCRIPTION_ID));
        $secret = $this->cacheManager->getItem(\sprintf('webhook_secret_%s', $subscriptionId));
        if (!$secret->isHit()) {
            throw new GoneHttpException('Unknown webhook subscription');
        }
        $hash = \hash_hmac('sha256', Type::string($request->getContent()), Type::string($secret->get()));
        if ($hash !== $signature) {
            throw new InvalidSignatureException();
        }
    }
}
