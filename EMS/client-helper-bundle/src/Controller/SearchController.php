<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\ClientHelperBundle\Helper\Search\Manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class SearchController
{
    public function __construct(private readonly Manager $manager, private readonly Handler $handler, private readonly Environment $templating, private readonly CacheHelper $cacheHelper)
    {
    }

    public function handle(Request $request): Response
    {
        $result = $this->handler->handle($request);
        $search = $this->manager->search($request);

        $context = \array_merge($result['context'], $search);

        $response = new Response($this->templating->render($result['template'], $context), Response::HTTP_OK);
        $this->cacheHelper->makeResponseCacheable($request, $response);

        return $response;
    }
}
