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
        $template = $this->handler->handle($request);

        $search = $this->manager->search($request);
        $template->context()->append($search);

        $response = new Response($template->render());
        $this->cacheHelper->makeResponseCacheable($request, $response);

        return $response;
    }
}
