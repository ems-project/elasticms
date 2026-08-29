<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Tests\Unit\Mcp;

use EMS\CoreBundle\Mcp\ElasticmsMcpToolDataService;
use PHPUnit\Framework\TestCase;

final class SaveDocumentOutputSchemaTest extends TestCase
{
    public function testOutputSchemaUsesRawDataSchemaAndArchivedFlag(): void
    {
        $rawDataSchema = [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
            ],
            'additionalProperties' => true,
        ];

        $method = new \ReflectionMethod(ElasticmsMcpToolDataService::class, 'finalizeSaveDocumentOutputSchema');
        $schema = $method->invoke(null, $rawDataSchema);

        self::assertSame('boolean', $schema['properties']['archived']['type']);
        self::assertSame($rawDataSchema, $schema['properties']['rawData']);
        self::assertSame(['contentType', 'ouuid', 'revisionId', 'draft', 'archived', 'rawData'], $schema['required']);
    }
}
