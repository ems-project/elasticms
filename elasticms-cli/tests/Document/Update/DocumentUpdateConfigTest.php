<?php

declare(strict_types=1);

namespace App\CLI\Tests\Document\Update;

use App\CLI\Client\Document\Update\DocumentUpdateConfig;
use PHPUnit\Framework\TestCase;

final class DocumentUpdateConfigTest extends TestCase
{
    public function testEmptyConfig()
    {
        $this->expectExceptionMessage('The required options "update[contentType]", "update[indexEmsId]" are missing');
        new DocumentUpdateConfig([]);
    }

    public function testUpdateConfigWithMapping()
    {
        $config = DocumentUpdateConfig::fromFile(__DIR__.'/updateConfig.json');

        $this->assertSame('page', $config->updateContentType);
        $this->assertSame(0, $config->updateIndexEmsId);
        $this->assertSame('title', $config->updateMapping[0]->field);
        $this->assertSame(1, $config->updateMapping[0]->indexDataColumn);
        $this->assertSame('description', $config->updateMapping[1]->field);
        $this->assertSame(2, $config->updateMapping[1]->indexDataColumn);
        $this->assertSame(5, $config->dataColumns[0]->columnIndex);
    }

    public function testInvalidFile()
    {
        $path = __DIR__.'/test.json';
        $this->expectExceptionMessage(\sprintf('File "%s" does not exits', $path));
        DocumentUpdateConfig::fromFile($path);
    }

    public function testInvalidDataColumnType()
    {
        $this->expectExceptionMessage('Invalid column type "test", allowed type "businessId"');

        new DocumentUpdateConfig([
            'update' => ['contentType' => 'page', 'indexEmsId' => 1],
            'dataColumns' => [
                ['index' => 1, 'type' => 'test'],
            ],
        ]);
    }
}
