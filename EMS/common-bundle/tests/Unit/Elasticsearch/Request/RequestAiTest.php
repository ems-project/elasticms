<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Elasticsearch\Request;

use EMS\CommonBundle\Elasticsearch\Request\Request;
use PHPUnit\Framework\TestCase;

final class RequestAiTest extends TestCase
{
    public function testGetScroll(): void
    {
        $request = new Request('test_index', []);
        $this->assertEquals('30s', $request->getScroll());
    }

    public function testSetSize(): void
    {
        $request = new Request('test_index', []);
        $request->setSize(20);

        $data = $request->toArray();
        $this->assertEquals(20, $data['size']);
    }

    public function testToArray(): void
    {
        $body = ['query' => ['match_all' => new \stdClass()]];
        $request = new Request('test_index', $body);

        $expected = [
            'body' => $body,
            'index' => 'test_index',
            'scroll' => '30s',
            'size' => 10,
        ];

        $this->assertEquals($expected, $request->toArray());
    }
}
