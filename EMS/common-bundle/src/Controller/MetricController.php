<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Controller;

use EMS\CommonBundle\Common\Metric\MetricCollector;
use Prometheus\RenderTextFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MetricController extends AbstractController
{
    public const string METRICS = 'ems.controller.metric::metrics';

    public function __construct(private readonly MetricCollector $metricCollector, private readonly ?string $metricPort)
    {
    }

    public function metrics(Request $request): Response
    {
        if (null !== $this->metricPort && $this->metricPort !== $request->server->get('SERVER_PORT')) {
            throw $this->createNotFoundException();
        }

        $metrics = $this->metricCollector->getMetrics();

        $renderFormat = new RenderTextFormat();
        $content = $renderFormat->render($metrics);

        return new Response($content, Response::HTTP_OK, ['Content-type' => RenderTextFormat::MIME_TYPE]);
    }
}
