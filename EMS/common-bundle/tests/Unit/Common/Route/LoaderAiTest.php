<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Route;

use EMS\CommonBundle\Common\Route\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class LoaderAiTest extends TestCase
{
    public function testLoadWithMetricsEnabled(): void
    {
        $loader = new Loader(true);
        $routes = $loader->load();

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(1, $routes);

        /** @var Route $route */
        $route = $routes->get('ems_metrics');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/metrics', $route->getPath());
        $this->assertEquals(['GET'], $route->getMethods());
        $this->assertEquals('%ems.metric.host%', $route->getHost());
        $this->assertEquals('ems.controller.metric::metrics', $route->getDefault('_controller'));
    }

    public function testLoadWithMetricsDisabled(): void
    {
        $loader = new Loader(false);
        $routes = $loader->load();

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(0, $routes);
    }
}
