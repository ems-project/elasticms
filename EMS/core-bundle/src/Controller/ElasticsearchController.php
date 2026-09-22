<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\Log\LocalizedLoggerInterface;
use EMS\CommonBundle\Elasticsearch\Response\Response as CommonResponse;
use EMS\CommonBundle\Search\Search as CommonSearch;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Core\Dashboard\DashboardManager;
use EMS\CoreBundle\Core\Document\DataLinks;
use EMS\CoreBundle\Core\UI\Page\Navigation;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\Dashboard;
use EMS\CoreBundle\Entity\Form\ExportDocuments;
use EMS\CoreBundle\Entity\Form\Search;
use EMS\CoreBundle\Entity\Form\SearchFilter;
use EMS\CoreBundle\Entity\UserInterface;
use EMS\CoreBundle\Form\Field\IconTextType;
use EMS\CoreBundle\Form\Field\SubmitEmsType;
use EMS\CoreBundle\Form\Form\ExportDocumentsType;
use EMS\CoreBundle\Repository\MessengerMessagesRepository;
use EMS\CoreBundle\Repository\SearchRepository;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\AssetExtractorService;
use EMS\CoreBundle\Service\ContentTypeService;
use EMS\CoreBundle\Service\DataService;
use EMS\CoreBundle\Service\EnvironmentService;
use EMS\CoreBundle\Service\IndexService;
use EMS\CoreBundle\Service\JobService;
use EMS\CoreBundle\Service\Revision\RevisionService;
use EMS\CoreBundle\Service\SearchService;
use EMS\Helpers\Standard\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

class ElasticsearchController extends AbstractController
{
    public function __construct(
        private readonly LocalizedLoggerInterface $logger,
        private readonly IndexService $indexService,
        private readonly ElasticaService $elasticaService,
        private readonly DataService $dataService,
        private readonly AssetExtractorService $assetExtractorService,
        private readonly EnvironmentService $environmentService,
        private readonly ContentTypeService $contentTypeService,
        private readonly RevisionService $revisionService,
        private readonly SearchService $searchService,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly JobService $jobService,
        private readonly DashboardManager $dashboardManager,
        private readonly SearchRepository $searchRepository,
        private readonly TranslatorInterface $translator,
        private readonly SerializerInterface $serializer,
        private readonly MessengerMessagesRepository $messengerMessagesRepository,
        private readonly ?string $healthCheckAllowOrigin,
        private readonly string $templateNamespace
    ) {
    }

    public function addAlias(string $name, Request $request): Response
    {
        $form = $this->createFormBuilder([])->add('name', IconTextType::class, [
            'icon' => 'fa fa-key',
            'required' => true,
        ])->add('save', SubmitEmsType::class, [
            'label' => 'Add',
            'icon' => 'fa fa-plus',
            'attr' => [
                'class' => 'btn btn-primary pull-right',
                'data-testid' => 'btn-action-save',
            ],
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $aliasName = $form->get('name')->getData();
            $this->indexService->updateAlias($aliasName, [], [$name]);

            $this->logger->messageNotice(t('message.alias_added', [
                'alias_name' => $aliasName,
                'index_name' => $name,
            ], 'emsco-core'));

            return $this->redirectToRoute(Routes::ADMIN_ENVIRONMENT_INDEX);
        }

        return $this->render(\sprintf('@%s/elasticsearch/add-alias.html.twig', $this->templateNamespace), [
            'form' => $form->createView(),
            'name' => $name,
            'title' => t('type.title_create', ['type' => 'alias', 'label' => $name], 'emsco-core'),
            'subTitle' => t('type.title_sub', ['type' => 'alias'], 'emsco-core'),
            'breadcrumb' => Navigation::admin()->environments()->add(
                label: t('key.orphan_indexes', [], 'emsco-core'),
                icon: 'fa fa-chain-broken',
                route: Routes::ADMIN_ELASTIC_ORPHAN
            )->add(t('type.title_create', ['type' => 'alias', 'label' => $name], 'emsco-core')),
            'notice' => t('type.notice_message', ['type' => 'alias'], 'emsco-core'),
        ]);
    }

    public function status(Request $request, string $_format, bool $detailed = true): Response
    {
        if ($detailed && !$this->authorizationChecker->isGranted('ROLE_USER')) {
            $detailed = false;
        }
        $statusCode = 200;
        $context = [];
        try {
            $health = $this->elasticaService->getClusterHealth();
            $context['cluster'] = $detailed ? $health : null;
            $context['cluster']['status'] = $status = $health['status'] ?? 'red';
            $context['cluster']['title'] = $this->translator->trans('cluster.status', ['color' => $status], 'emsco-core');
            if ('red' === $status) {
                $statusCode = 500;
            }
        } catch (\Throwable $throwable) {
            $status = 'red';
            $context['cluster']['title'] = $throwable->getMessage();
            $statusCode = 503;
        }
        $context['status'] = $status;
        $context['title'] = $context['cluster']['title'];

        try {
            $jobFailed = $this->jobService->countFailed();
            $jobPending = $this->jobService->countPending();
            $jobStatus = $jobFailed > 0 ? 'yellow' : 'green';
            $jobPendingLimit = (int) $request->query->get('jobs_pending_limit', '0');
            if ($jobPendingLimit > 0 && $jobPending >= $jobPendingLimit) {
                $jobStatus = 'red';
                $statusCode = 500;
            }
            $jobFailedLimit = (int) $request->query->get('jobs_failed_limit', '0');
            if ($jobFailedLimit > 0 && $jobFailed >= $jobFailedLimit) {
                $jobStatus = 'red';
                $statusCode = 500;
            }
            $context['jobs']['pending'] = $jobPending;
            $context['jobs']['failed'] = $jobFailed;
            $context['jobs']['status'] = $jobStatus;
        } catch (\Throwable) {
        }

        try {
            $messageFailed = $this->messengerMessagesRepository->errorCount();
            $messageQueue = $this->messengerMessagesRepository->waitingCount() - $messageFailed;
            $messageStatus = $messageFailed > 0 ? 'yellow' : 'green';
            $busQueueLimit = (int) $request->query->get('bus_queue_limit', '0');
            if ($busQueueLimit > 0 && $messageQueue >= $busQueueLimit) {
                $messageStatus = 'red';
                $statusCode = 500;
            }
            $busFailedLimit = (int) $request->query->get('bus_failed_limit', '0');
            if ($busFailedLimit > 0 && $messageFailed >= $busFailedLimit) {
                $messageStatus = 'red';
                $statusCode = 500;
            }
            $context['bus']['queue'] = $messageQueue;
            $context['bus']['failed'] = $messageFailed;
            $context['bus']['status'] = $messageStatus;
        } catch (\Throwable) {
        }

        if ($detailed) {
            $context['cluster'] = \array_merge($context['cluster'], $this->elasticaService->getClusterInfo());

            $context['certificate'] = $this->dataService->getCertificateInfo();
            $context['certificate']['title'] = $this->translator->trans('certificate.status', ['color' => $context['certificate']['status'] ?? 'red'], 'emsco-core');

            try {
                $context['asset_extractor'] = $this->assetExtractorService->hello();
                $context['asset_extractor']['status'] = 'green';
                $context['asset_extractor']['title'] = $this->translator->trans('asset_extractor.status', ['color' => 'green'], 'emsco-core');
            } catch (\Exception $e) {
                $context['asset_extractor'] = [
                    'status' => 'red',
                    'title' => $this->translator->trans('asset_extractor.status', ['color' => 'red'], 'emsco-core'),
                    'message' => $e->getMessage(),
                ];
            }
        }

        $htmlTemplate = \sprintf('@%s/elasticsearch/status.html.twig', $this->templateNamespace);
        $response = match ($_format) {
            'json' => new JsonResponse(\array_filter(\array_merge($context, [
                'body' => $this->renderBlock($htmlTemplate, 'status', $context)->getContent(),
            ]))),
            'xml' => new Response($this->serializer->serialize($context, 'xml'), Response::HTTP_OK, ['Content-Type' => 'application/xml']),
            default => $this->render($htmlTemplate, \array_filter(\array_merge($context, [
                'title' => t('title.systems_status', [], 'emsco-core'),
                'subTitle' => t('title.systems_status_tagline', [], 'emsco-core'),
                'breadcrumb' => new Navigation()->add(
                    label: t('title.systems_status', [], 'emsco-core'),
                    icon: 'fa-solid fa-stethoscope',
                ),
            ]))),
        };
        $response->setStatusCode($statusCode);

        $allowOrigin = $this->healthCheckAllowOrigin;
        if (\is_string($allowOrigin) && '' !== $allowOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        }

        return $response;
    }

    public function quickSearch(Request $request): Response
    {
        $dashboard = $this->dashboardManager->getDefinition(Dashboard::DEFINITION_QUICK_SEARCH);
        if (null !== $dashboard) {
            return $this->redirectToRoute(Routes::DASHBOARD, ['name' => $dashboard->getName(), 'q' => $query = $request->query->get('q', '')]);
        }
        $query = $request->query->get('q');
        $search = $this->searchRepository->findOneBy([
            'default' => true,
        ]);
        if ($search instanceof Search) {
            /** @var SearchFilter $filter */
            foreach ($search->getFilters() as &$filter) {
                if (empty($filter->getPattern())) {
                    $filter->setPattern($query);
                }
            }
        } else {
            $search = new Search();
            $search->setEnvironments($this->environmentService->getEnvironmentNames());
            if (false !== $query) {
                $search->getFirstFilter()->setPattern($query)->setBooleanClause('must');
            }
        }

        return $this->forward(ElasticsearchController::class.'::search', [
            'query' => null,
        ], [
            'search_form' => $search->jsonSerialize(),
        ]);
    }

    public function legacySearch(Request $request, DataLinks $dataLinks): void
    {
        $environments = Type::string($request->query->get('environment', ''));
        $searchId = $dataLinks->getSearchId();
        $category = $request->query->get('category');
        $assetName = $request->query->get('asset_name');
        $circleOnly = $request->query->get('circle');
        $dataLink = $request->query->get('dataLink');

        if (\is_string($dataLink)) {
            $emsLink = EMSLink::fromText($dataLink);
            $contentType = $this->contentTypeService->giveByName($emsLink->getContentType());
            $document = $this->searchService->getDocument($contentType, $emsLink->getOuuid());

            $dataLinks->addContentTypes($contentType);
            $dataLinks->addDocument(document: $document, displayLabel: $this->revisionService->display($document));

            return;
        }

        $contentTypes = $dataLinks->getContentTypeNames();

        $search = null;
        if ($searchId) {
            $search = $this->searchRepository->findOneBy(['id' => $searchId]);
        }

        if (!$search instanceof Search) {
            $search = $this->searchService->getDefaultSearch($contentTypes);
        }

        $searchContentTypes = $search->getContentTypes();
        foreach ($searchContentTypes as $searchContentType) {
            $dataLinks->addContentTypes($this->contentTypeService->giveByName($searchContentType));
        }

        if ($assetName) {
            $allContentTypes = $this->contentTypeService->getAll();
            // For search only in contentType with Asset field == $assetName.
            $contentTypes = [];
            foreach ($allContentTypes as $contentType) {
                if ($contentType->hasAssetField()) {
                    $contentTypes[] = $contentType->getName();
                }
            }
        }

        if ([] !== $contentTypes) {
            $search->setContentTypes($contentTypes);
        }

        if (!empty($environments) && null === $searchId) {
            $search->setEnvironments(\explode(',', $environments));
        }

        $search->setSearchPattern($dataLinks->getPattern(), true);
        $commonSearch = $this->searchService->generateSearch($search);

        if ($circleOnly && !$this->authorizationChecker->isGranted('ROLE_USER_MANAGEMENT')) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $circles = $user->getCircles();

            $ouuids = [];
            foreach ($circles as $circle) {
                \preg_match('/(?P<type>\w+):(?P<ouuid>\w+)/', (string) $circle, $matches);
                if (isset($matches['ouuid'])) {
                    $ouuids[] = $matches['ouuid'];
                }
            }
            $query = $commonSearch->getQuery();
            $boolQuery = $this->elasticaService->getBoolQuery();
            if (!$query instanceof $boolQuery) {
                if (null !== $query) {
                    $boolQuery->addMust($query);
                }
                $query = $boolQuery;
            }
            $query->addMust($this->elasticaService->getTermsQuery('_id', $ouuids));
            $commonSearch = new CommonSearch($commonSearch->getIndices(), $query);
        }

        if (null !== $category && 1 === \count($contentTypes)) {
            $contentType = $this->contentTypeService->getByName(\array_first($contentTypes));
            if (false !== $contentType && $contentType->hasCategoryField()) {
                $categoryField = $contentType->giveCategoryField();
                $boolQuery = $this->elasticaService->getBoolQuery();
                $query = $commonSearch->getQuery();
                if (!$query instanceof $boolQuery) {
                    if (null !== $query) {
                        $boolQuery->addMust($query);
                    }
                    $query = $boolQuery;
                }
                $query->addMust($this->elasticaService->getTermsQuery($categoryField, [$category]));
                $commonSearch = new CommonSearch($commonSearch->getIndices(), $query);
            }
        }

        $commonSearch->setFrom($dataLinks->getFrom());
        $commonSearch->setSize($dataLinks->getSize());

        $response = CommonResponse::fromResultSet($this->elasticaService->search($commonSearch));

        $dataLinks->setTotal($response->getTotal());
        foreach ($response->getDocuments() as $document) {
            $dataLinks->addDocument(document: $document, displayLabel: $this->revisionService->display($document));
        }
    }

    public function export(Request $request, ContentType $contentType): Response
    {
        $exportDocuments = new ExportDocuments($contentType, $this->generateUrl('emsco_search_export', ['contentType' => $contentType]), '{}');
        $form = $this->createForm(ExportDocumentsType::class, $exportDocuments);
        $form->handleRequest($request);

        /** @var ExportDocuments */
        $exportDocuments = $form->getData();
        $command = \sprintf(
            "%s %s %s '%s'%s --environment=%s --baseUrl=%s",
            Commands::CONTENT_TYPE_EXPORT,
            $contentType->getName(),
            $exportDocuments->getFormat(),
            $exportDocuments->getQuery(),
            $exportDocuments->isWithBusinessKey() ? ' --withBusinessId' : '',
            $exportDocuments->getEnvironment(),
            '//'.$request->getHttpHost()
        );
        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('Unexpected user object');
        }

        $job = $this->jobService->createCommand($user, $command);

        return $this->redirectToRoute('emsco_job_status', [
            'job' => $job->getId(),
        ]);
    }
}
