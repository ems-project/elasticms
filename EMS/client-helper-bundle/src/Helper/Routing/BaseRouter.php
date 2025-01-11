<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

abstract class BaseRouter implements RouterInterface, RequestMatcherInterface
{
    protected RequestContext $context;
    protected RouteCollection $collection;
    protected ?UrlMatcher $matcher = null;
    protected ?UrlGenerator $generator = null;

    #[\Override]
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    #[\Override]
    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public function match($pathInfo): array
    {
        return $this->getMatcher()->match($pathInfo);
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public function matchRequest(Request $request): array
    {
        return $this->getMatcher()->matchRequest($request);
    }

    /**
     * @param array<string, string> $parameters
     */
    #[\Override]
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    private function getMatcher(): UrlMatcher
    {
        if (null === $this->matcher) {
            $this->matcher = new UrlMatcher($this->getRouteCollection(), $this->getContext());
        }

        return $this->matcher;
    }

    private function getGenerator(): UrlGenerator
    {
        if (null === $this->generator) {
            $this->generator = new UrlGenerator($this->getRouteCollection(), $this->getContext());
        }

        return $this->generator;
    }
}
