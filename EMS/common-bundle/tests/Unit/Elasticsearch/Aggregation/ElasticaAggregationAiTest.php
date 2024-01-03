<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Elasticsearch\Aggregation;

use EMS\CommonBundle\Elasticsearch\Aggregation\ElasticaAggregation;
use PHPUnit\Framework\TestCase;

final class ElasticaAggregationAiTest extends TestCase
{
    public function testElasticaAggregation(): void
    {
        $name = 'test_aggregation';
        $aggregation = new ElasticaAggregation($name);

        $this->assertEquals($name, $aggregation->getName());

        $basename = 'reverse_nested';
        $params = ['param1' => 'value1', 'param2' => 'value2'];
        $aggregation->setConfig($basename, $params);

        $expectedArray = [
            $name => [
                $basename => $params,
            ],
        ];

        if ('reverse_nested' === $basename) {
            $expectedArray[$name][$basename] = (object) $params;
        }

        $this->assertEquals($expectedArray[$name], $aggregation->toArray());
    }

    public function testGetBaseNameWithoutSettingConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected null aggregation');

        $aggregation = new ElasticaAggregation('test_aggregation');
        $aggregation->toArray(); // This will trigger the _getBaseName method
    }
}
