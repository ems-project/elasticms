<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use EMS\ClientHelperBundle\Contracts\Request\HandlerInterface;
use EMS\ClientHelperBundle\Exception\SingleResultException;
use EMS\ClientHelperBundle\Exception\TemplatingException;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequest;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use EMS\ClientHelperBundle\Helper\Templating\TemplateDocument;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Contracts\Twig\TemplateFactoryInterface;
use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouterInterface;

final readonly class Handler implements HandlerInterface
{
    private ClientRequest $clientRequest;

    public function __construct(
        ClientRequestManager $manager,
        private TemplateFactoryInterface $templateFactory,
        private RouterInterface $router,
        private ?Profiler $profiler,
    ) {
        $this->clientRequest = $manager->getDefault();
    }

    public function handle(Request $request): TemplateInterface
    {
        $emschRequest = EmschRequest::fromRequest($request);

        if (null !== $this->profiler && !$emschRequest->isProfilerEnabled()) {
            $this->profiler->disable();
        }

        $route = $this->getRoute($emschRequest);
        $context = ['trans_default_domain' => $this->clientRequest->getCacheKey()];

        if (null !== $document = $this->getDocument($request, $route)) {
            $context['document'] = $document;
            $context['source'] = $document['_source'];
            $context['emsLink'] = EMSLink::fromDocument($document);
        }

        return $this->templateFactory->create(
            templateName: $this->getTemplateName($request, $route, $document),
            context: $context
        );
    }

    private function getRoute(Request $request): SymfonyRoute
    {
        $name = $request->attributes->get('_route');
        $route = $this->router->getRouteCollection()->get($name);

        if (null === $route) {
            throw new NotFoundHttpException(\sprintf('ems route "%s" not found', $name));
        }

        return $route;
    }

    /**
     * @return array{_id: string, _type?: string, _source: array<mixed>}|null
     */
    public function getDocument(Request $request, SymfonyRoute $route): ?array
    {
        $query = $route->getOption('query');

        if (null === $query) {
            return null;
        }

        $json = RequestHelper::replace($request, $query);

        $indexRegex = $route->getOption('index_regex');
        if (null !== $indexRegex) {
            $indexRegex = RequestHelper::replace($request, $indexRegex);
        }

        try {
            return $this->clientRequest->searchOne($route->getOption('type'), Json::decode($json), $indexRegex);
        } catch (SingleResultException) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param ?array<mixed> $document
     */
    private function getTemplateName(Request $request, SymfonyRoute $route, ?array $document = null): string
    {
        $template = $route->getOption('template');

        if (!\is_string($template)) {
            throw new TemplatingException('Provide a valid string as template!');
        }

        $template = RequestHelper::replace($request, $template);

        if (null === $document || TemplateDocument::PREFIX === \substr($template, 0, 6)) {
            return $template;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $value = $propertyAccessor->getValue($document, '[_source]'.$template);

        if (null === $value) {
            throw new TemplatingException(\sprintf('Could not access property %s in source', $template));
        }

        return TemplateDocument::PREFIX.'/'.$value;
    }
}
