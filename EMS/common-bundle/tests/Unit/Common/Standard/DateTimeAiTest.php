<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Standard;

use EMS\CommonBundle\Common\Standard\DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeAiTest extends TestCase
{
    public function testCreate(): void
    {
        $time = '2023-10-06 12:00:00';
        $dateTime = DateTime::create($time);

        $this->assertInstanceOf(\DateTimeInterface::class, $dateTime);
        $this->assertEquals($time, $dateTime->format('Y-m-d H:i:s'));
    }

    public function testCreateInvalidTime(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed creating time for "invalid-time"');

        DateTime::create('invalid-time');
    }

    public function testCreateFromFormat(): void
    {
        $time = '2023-10-06T12:00:00+00:00';
        $format = \DateTimeInterface::ATOM;
        $dateTime = DateTime::createFromFormat($time, $format);

        $this->assertInstanceOf(\DateTimeInterface::class, $dateTime);
        $this->assertEquals($time, $dateTime->format($format));
    }

    public function testCreateFromFormatInvalidTime(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Failed creating dateTime for "invalid-time" with format ".*", \[.*\]$/');

        DateTime::createFromFormat('invalid-time', \DateTimeInterface::ATOM);
    }
}
