<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Data\Revision;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

class RevisionAiTest extends TestCase
{
    public function testConstructAndGetters(): void
    {
        $resultData = [
            'id' => 123,
            'ouuid' => 'test-ouuid',
            'revision' => ['key' => 'value'],
        ];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($resultData);

        $revision = new Revision($result);

        $this->assertEquals(123, $revision->getRevisionId());
        $this->assertEquals('test-ouuid', $revision->getOuuid());
        $this->assertEquals(['key' => 'value'], $revision->getRawData());
    }
}
