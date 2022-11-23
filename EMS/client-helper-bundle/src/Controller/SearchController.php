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
    private Manager $manager;
    private Handler $handler;
    private Environment $templating;
    private CacheHelper $cacheHelper;

    public function __construct(Manager $manager, Handler $handler, Environment $templating, CacheHelper $cacheHelper)
    {
        $this->manager = $manager;
        $this->handler = $handler;
        $this->templating = $templating;
        $this->cacheHelper = $cacheHelper;
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
