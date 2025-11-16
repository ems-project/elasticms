<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\CommonBundle\Common\Cache\Cache;
use EMS\CommonBundle\Common\HttpCache\HttpCacheManager;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpCacheController extends AbstractController
{
    public function __construct(
        private HttpCacheManager $httpCacheManager,
        private Cache $cacheManager,
    ) {
    }

    public function adminWebhook(Request $request): Response
    {
        $event = Json::decode(Type::string($request->getContent()));
        $eventName = $event['event'] ?? null;
        $data = $event['data'] ?? null;
        if (!\is_string($eventName)) {
            throw new \RuntimeException('event name not provided');
        }
        if (!\is_array($data)) {
            throw new \RuntimeException('event data not provided');
        }

        if (\str_starts_with($eventName, 'content.published.')) {
            $ouuid = Type::string($data['ouuid']);
            $this->httpCacheManager->purgeByTags($ouuid);
        } else {
            throw new \RuntimeException('event type not supported');
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
