<?php
require __DIR__ . '/vendor/autoload.php';

use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$endpoint  = 'http://localhost:8200/v1/traces';      // adapte si besoin
$protocol  = 'application/x-protobuf';

$transport = (new OtlpHttpTransportFactory())->create($endpoint, $protocol);
$exporter  = new SpanExporter($transport);

$clock     = ClockFactory::getDefault();
$processor = new BatchSpanProcessor($exporter, $clock);

$provider  = new TracerProvider($processor);
$tracer    = $provider->getTracer('cli-test');

$span = $tracer->spanBuilder('demo-span')->startSpan();
$span->addEvent('hello');
usleep(100000);
$span->end();

$provider->shutdown();
echo "done\n";
