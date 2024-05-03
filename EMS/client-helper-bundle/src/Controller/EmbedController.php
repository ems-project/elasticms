<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class EmbedController extends AbstractController
{
    private readonly ClientRequest $clientRequest;

    public function __construct(ClientRequestManager $manager, private readonly CacheHelper $cacheHelper, private readonly Environment $templating)
    {
        $this->clientRequest = $manager->getDefault();
    }

    /**
     * @param string[]     $sourceFields
     * @param array<mixed> $args
     */
    public function renderHierarchy(string $template, string $parent, string $field, ?int $depth = null, array $sourceFields = [], array $args = [], ?string $cacheType = null): Response
    {
        $cacheKey = [
            'EMSCH_Hierarchy',
            $template,
            $parent,
            $field,
            $depth,
            $sourceFields,
            $args,
            $cacheType,
        ];

        return $this->clientRequest->getCacheResponse($cacheKey, $cacheType, function () use ($template, $parent, $field, $depth, $sourceFields, $args) {
            $hierarchy = $this->clientRequest->getHierarchy($parent, $field, $depth, $sourceFields, $args['activeChild'] ?? null);

            return $this->render($template, [
                'translation_domain' => $this->clientRequest->getCacheKey(),
                'args' => $args,
                'hierarchy' => $hierarchy,
            ]);
        });
    }

    /**
     * @param string|string[]|null $searchType
     * @param array<mixed>         $body
     * @param array<mixed>         $args
     * @param string[]             $sourceExclude
     */
    public function renderEmbed(string|array|null $searchType, array $body, string $template, array $args = [], int $from = 0, int $size = 10, ?string $cacheType = null, array $sourceExclude = []): Response
    {
        $cacheKey = [
            'EMSCH_Block',
            $searchType,
            $body,
            $template,
            $args,
            $from,
            $size,
            $cacheType,
            $sourceExclude,
        ];

        return $this->clientRequest->getCacheResponse($cacheKey, $cacheType, function () use ($searchType, $body, $template, $args, $from, $size, $sourceExclude) {
            $result = $this->clientRequest->search($searchType, $body, $from, $size, $sourceExclude);

            return $this->render($template, [
                'translation_domain' => $this->clientRequest->getCacheKey(),
                'args' => $args,
                'result' => $result,
            ]);
        });
    }

    /**
     * @param mixed[] $context
     */
    public function fragment(Request $request, string $template, array $context = []): Response
    {
        $response = new Response($this->templating->render($template, $context));
        $this->cacheHelper->makeResponseCacheable($request, $response);

        return $response;
    }

    /**
     * @param mixed[] $context
     */
    public function cacheableFragment(string $cacheType, string $template, array $context = []): Response
    {
        return $this->clientRequest->getCacheResponse([
            'template' => $template,
            'context' => $context,
        ], $cacheType, fn () => new Response($this->templating->render($template, $context)));
    }
}
