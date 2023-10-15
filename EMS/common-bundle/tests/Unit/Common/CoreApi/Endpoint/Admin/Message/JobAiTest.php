<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Common\Unit\CoreApi\Endpoint\Admin\Message;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException as InvalidOptionsExceptionAlias;

final class JobAiTest extends TestCase
{
    public function testConstructAndGetters(): void
    {
        $data = [
            'acknowledged' => true,
            'success' => true,
            'message' => 'Some message',
            'job_id' => '12345',
            'output' => 'Some output',
            'command' => 'Some command',
        ];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($data);

        $job = new Job($result);

        $this->assertSame('12345', $job->getJobId());
        $this->assertSame($result, $job->getResult());
        $this->assertSame('Some command', $job->getCommand());
        $this->assertSame('Some output', $job->getOutput());
    }

    public function testConstructWithMissingCommand(): void
    {
        $data = [
            'acknowledged' => true,
            'success' => true,
            'message' => 'Some message',
            'job_id' => '12345',
            'output' => 'Some output',
            'command' => null,
        ];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($data);

        $job = new Job($result);

        $this->assertSame('12345', $job->getJobId());
        $this->assertSame($result, $job->getResult());
        $this->assertSame('list', $job->getCommand());
        $this->assertSame('Some output', $job->getOutput());
    }

    public function testConstructWithInvalidData(): void
    {
        $this->expectException(InvalidOptionsExceptionAlias::class);

        $data = [
            'acknowledged' => true,
            'success' => true,
            'message' => 'Some message',
            'job_id' => 12345, // Invalid type
            'output' => 'Some output',
            'command' => 'Some command',
        ];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($data);

        new Job($result);
    }
}
