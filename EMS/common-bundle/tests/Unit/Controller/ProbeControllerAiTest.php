<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Controller;

use EMS\CommonBundle\Controller\ProbeController;
use EMS\CommonBundle\Service\ElasticaService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ProbeControllerAiTest extends TestCase
{
    private ElasticaService $elasticaService;
    private ProbeController $controller;

    #[\Override]
    protected function setUp(): void
    {
        $this->elasticaService = $this->createMock(ElasticaService::class);
        $this->controller = new ProbeController($this->elasticaService);
    }

    public function testReadiness(): void
    {
        $version = '7.10.0';
        $this->elasticaService->expects($this->once())
            ->method('getVersion')
            ->willReturn($version);

        $response = $this->controller->readiness();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['cluster_version' => $version], (array) \json_decode($response->getContent()));
    }

    public function testLiveness(): void
    {
        $response = $this->controller->liveness();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['live' => true], (array) \json_decode($response->getContent()));
    }
}
