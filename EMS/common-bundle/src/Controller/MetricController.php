<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Controller;

use EMS\CommonBundle\Common\Metric\MetricCollector;
use Prometheus\RenderTextFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class MetricController extends AbstractController
{
    public const METRICS = 'ems.controller.metric::metrics';

    public function __construct(private readonly MetricCollector $metricCollector, private readonly ?string $metricPort)
    {
    }

    public function metrics(): Response
    {
        if (null !== $this->metricPort && $this->metricPort !== $_SERVER['SERVER_PORT']) {
            throw $this->createNotFoundException();
        }

        $metrics = $this->metricCollector->getMetrics();

        $renderFormat = new RenderTextFormat();
        $content = $renderFormat->render($metrics);

        return new Response($content, Response::HTTP_OK, ['Content-type' => RenderTextFormat::MIME_TYPE]);
    }
}
