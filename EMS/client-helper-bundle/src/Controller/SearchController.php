<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\ClientHelperBundle\Helper\Search\Manager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class SearchController
{
    public function __construct(
        private Manager $manager,
        private Handler $handler,
        private CacheHelper $cacheHelper
    ) {
    }

    public function handle(Request $request): Response
    {
        @\trigger_error('The SearchController is deprecated and will be removed in ems 8, use the emsch_search_config and emsch_search_config_execute twig functions instead', E_USER_DEPRECATED);
        $template = $this->handler->handle($request);

        $search = $this->manager->searchFromRequest($request);
        $template->context()->append($search);

        $response = new Response($template->render());
        $this->cacheHelper->makeResponseCacheable($request, $response);

        return $response;
    }
}
