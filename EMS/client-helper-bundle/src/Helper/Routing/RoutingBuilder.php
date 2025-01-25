<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use EMS\ClientHelperBundle\Helper\Builder\AbstractBuilder;
use EMS\ClientHelperBundle\Helper\ContentType\ContentType;
use EMS\ClientHelperBundle\Helper\Environment\Environment;
use EMS\ClientHelperBundle\Helper\Templating\TemplateFiles;
use EMS\CommonBundle\Search\Search;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollection;

final class RoutingBuilder extends AbstractBuilder
{
    private const string CONFIG_PATH = __DIR__.'/../../Resources/config/routing/';

    public function buildRouteCollection(Environment $environment): RouteCollection
    {
        $settings = $this->settings($environment);

        $routeCollection = new RouteCollection();
        $routes = [];

        if ($environment->isLocalPulled()) {
            $routes = $environment->getLocal()->getRouting($settings)->createRoutes();
        } elseif (null !== $contentType = $settings->getRoutingContentType()) {
            $routes = $this->createRoutes($contentType);
        }

        if (0 === \count($routes)) {
            return $routeCollection;
        }

        $routePrefix = $environment->getRoutePrefix();
        foreach ($routes as $route) {
            $route->addToCollection($routeCollection, $this->locales, $routePrefix);
        }

        return $this->addEmschRoutes($routeCollection, $routePrefix);
    }

    public function buildFiles(Environment $environment, TemplateFiles $templateFiles, string $directory): void
    {
        RoutingFile::build($directory, $templateFiles, $this->getDocuments($environment));
    }

    /**
     * @return RoutingDocument[]
     */
    public function getDocuments(Environment $environment): array
    {
        if (null === $contentType = $this->settings($environment)->getRoutingContentType()) {
            return [];
        }

        return $this->searchDocuments($contentType);
    }

    #[\Override]
    protected function modifySearch(Search $search): void
    {
        $search->setSort(['order' => ['order' => 'asc', 'missing' => '_last', 'unmapped_type' => 'long']]);
    }

    /**
     * @return Route[]
     */
    private function createRoutes(ContentType $contentType): array
    {
        if (null !== $cache = $contentType->getCache()) {
            return $cache;
        }

        $routes = [];
        foreach ($this->searchDocuments($contentType) as $document) {
            $routes[] = Route::fromData($document->getName(), $document->getRouteData());
        }

        $contentType->setCache($routes);
        $this->clientRequest->cacheContentType($contentType);

        return $routes;
    }

    /**
     * @return RoutingDocument[]
     */
    private function searchDocuments(ContentType $contentType): array
    {
        $documents = [];

        foreach ($this->search($contentType)->getDocuments() as $document) {
            $documents[] = new RoutingDocument($document);
        }

        return $documents;
    }

    private function addEmschRoutes(RouteCollection $routes, ?string $routePrefix): RouteCollection
    {
        $routes->addCollection($this->getEmschRoutesFromFile(self::CONFIG_PATH.'core_api.xml', $routePrefix));

        return $routes;
    }

    private function getEmschRoutesFromFile(string $file, ?string $routePrefix = null): RouteCollection
    {
        $routes = new RouteCollection();
        $configRoutes = new XmlFileLoader(new FileLocator())->load($file);

        foreach ($configRoutes as $name => $route) {
            $prefixedRoute = $route->setPath($routePrefix.$route->getPath());
            $routes->add($name, $prefixedRoute);
        }

        return $routes;
    }
}
