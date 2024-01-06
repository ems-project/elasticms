<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Metric;

use Doctrine\DBAL\Connection;
use EMS\CommonBundle\Common\Metric\MetricCollectorInterface;
use EMS\Helpers\Standard\DateTime;
use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use Prometheus\CollectorRegistry;

final class SubmissionMetricCollector implements MetricCollectorInterface
{
    private const VALID_UNTIL = '+5 minutes';
    private const GAUGES = [
        'total' => 'Total form submissions',
        'unprocessed_total' => 'Total unprocessed submissions',
        'errors_total' => 'Total count error submissions',
    ];

    public function __construct(private readonly FormSubmissionRepository $formSubmissionRepository, private readonly Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'emss_submissions';
    }

    public function validUntil(): int
    {
        return DateTime::create(self::VALID_UNTIL)->getTimestamp();
    }

    public function collect(CollectorRegistry $collectorRegistry): void
    {
        if (!$this->hasDatabaseConnection()) {
            return;
        }

        $metrics = $this->formSubmissionRepository->getMetrics();
        $namespace = $this->getName();

        foreach (self::GAUGES as $gaugeName => $gaugeHelp) {
            $gauge = $collectorRegistry->getOrRegisterGauge(
                $namespace,
                $gaugeName,
                $gaugeHelp,
                ['form_instance', 'form_name']
            );

            foreach ($metrics as $data) {
                $gauge->set($data[$gaugeName], [$data['instance'], $data['name']]);
            }
        }
    }

    private function hasDatabaseConnection(): bool
    {
        try {
            $this->connection->connect();

            return $this->connection->isConnected();
        } catch (\Throwable) {
            return false;
        }
    }
}
