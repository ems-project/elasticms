<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Dashboard\Services;

use EMS\CommonBundle\Storage\StorageManager;
use EMS\CoreBundle\Core\Dashboard\DashboardOptions;
use EMS\CoreBundle\Entity\Dashboard;
use EMS\CoreBundle\Entity\Form\Search;
use EMS\CoreBundle\Form\Form\SearchFormType;
use EMS\CoreBundle\Routes;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class AdvancedSearch implements DashboardInterface
{
    public function __construct(
        private readonly Environment $twig,
        private RequestStack $requestStack,
        private readonly FormFactory $formFactory,
        private readonly RouterInterface $router,
        private readonly StorageManager $storageManager,
        private readonly string $templateNamespace
    ) {
    }

    public function getResponse(Dashboard $dashboard): Response
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            throw new \RuntimeException('Request must be set');
        }
        if (Request::METHOD_POST === $request->getMethod()) {
            $searchForm = Type::array($request->request->all()['search_form']);
            unset($searchForm['search']);
            $uid = $this->storageManager->saveConfig($searchForm);

            return new RedirectResponse($this->router->generate(Routes::DASHBOARD, [
                'uid' => $uid,
                'name' => $dashboard->getName(),
            ]));
        }

        $options = $dashboard->getOptions();
        $uid = $request->query->get('uid');
        $search = $this->getDefaultSearch($options);

        $response = new Response();
        $form = $this->formFactory->create(SearchFormType::class, $search);
        if (\is_string($uid)) {
            $data = $this->storageManager->getConfig($uid);
            $form->submit($data);
        }

        $response->setContent($this->twig->render(\sprintf('@%s/dashboard/advanced-search/render.html.twig', $this->templateNamespace), [
            'dashboard' => $dashboard,
            'form' => $form->createView(),
            'options' => $options,
        ]));

        return $response;
    }

    private function getDefaultSearch(DashboardOptions $options): Search
    {
        $search = new Search();
        $search->setEnvironments(Type::array($options->offsetGet(DashboardOptions::ENVIRONMENTS) ?? []));
        $search->setContentTypes(Type::array($options->offsetGet(DashboardOptions::CONTENT_TYPES) ?? []));
        $search->setSortBy($options->getNullableString(DashboardOptions::SORT_BY));
        $search->setSortOrder($options->getNullableString(DashboardOptions::SORT_ORDER));
        $search->setMinimumShouldMatch($options->getInteger(DashboardOptions::MINIMUM_SHOULD_MATCH, 1));

        return $search;
    }
}
