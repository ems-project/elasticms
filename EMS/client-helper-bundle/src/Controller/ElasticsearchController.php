<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Elasticsearch\Client;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ElasticsearchController
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Client $client,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->handler->handleStaticTemplate($request)?->renderBlock('preRequest');

        $path = '/'.\rtrim($request->attributes->getString('path'), '/');
        $index = $request->attributes->get('index');

        $data = '' !== $request->getContent() ? Json::decode($request->getContent()) : [];
        $query = $request->query->all();

        if (null !== $index && !\preg_match('/^(?![_-])[a-z0-9_-]{1,255}$/', (string) $index)) {
            throw new \InvalidArgumentException('Invalid index name: '.$index);
        }

        return $this->request($index.$path, 'GET', $data, $query);
    }

    public function scroll(Request $request): JsonResponse
    {
        $this->handler->handleStaticTemplate($request)?->renderBlock('preRequest');

        $method = $request->getMethod();
        $data = '' !== $request->getContent() ? Json::decode($request->getContent()) : [];

        $scroll = $data['scroll'] ?? null;
        $scrollId = $data['scroll_id'] ?? null;

        if (null === $scrollId) {
            throw new \InvalidArgumentException("Missing 'scroll_id'");
        }
        if (Request::METHOD_GET === $method && null === $scroll) {
            throw new \InvalidArgumentException("Missing 'scroll'");
        }

        return $this->request('_search/scroll', $method, match ($method) {
            Request::METHOD_GET => ['scroll' => $scroll, 'scroll_id' => $scrollId],
            Request::METHOD_DELETE => ['scroll_id' => $scrollId],
            default => throw new BadRequestHttpException(),
        });
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $query
     */
    private function request(string $path, string $method, array $data = [], array $query = []): JsonResponse
    {
        $response = $this->client->request($method, $path, $data, $query);

        return new JsonResponse($response->getData(), $response->getStatus());
    }
}
