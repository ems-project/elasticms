<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\Data;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Data\Draft;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

class DraftAiTest extends TestCase
{
    public function testConstructAndGetters(): void
    {
        $resultData = [
            'revision_id' => 123,
            'ouuid' => 'test-ouuid',
        ];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($resultData);

        $draft = new Draft($result);

        $this->assertEquals(123, $draft->getRevisionId());
        $this->assertEquals('test-ouuid', $draft->getOuuid());
    }

    public function testConstructWithMissingOuuid(): void
    {
        $resultData = [
            'revision_id' => 456,
        ];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($resultData);

        $draft = new Draft($result);

        $this->assertEquals(456, $draft->getRevisionId());
        $this->assertNull($draft->getOuuid());
    }
}
