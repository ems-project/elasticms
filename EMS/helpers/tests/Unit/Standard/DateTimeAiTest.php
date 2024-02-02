<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeAiTest extends TestCase
{
    public function testCreate()
    {
        $timeString = '2024-01-01 00:00:00';
        $dateTime = DateTime::create($timeString);
        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime);
        $this->assertEquals('2024-01-01T00:00:00+00:00', $dateTime->format(\DateTimeInterface::ATOM));
    }

    public function testCreateThrowsRuntimeExceptionOnInvalidTime()
    {
        $this->expectException(\RuntimeException::class);
        DateTime::create('invalid-time-string');
    }

    public function testCreateFromFormat()
    {
        $timeString = '2024-01-01T00:00:00+00:00';
        $dateTime = DateTime::createFromFormat($timeString, \DateTimeInterface::ATOM);
        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime);
        $this->assertEquals($timeString, $dateTime->format(\DateTimeInterface::ATOM));
    }

    public function testCreateFromFormatThrowsRuntimeExceptionOnInvalidFormat()
    {
        $this->expectException(\RuntimeException::class);
        DateTime::createFromFormat('2024-01-01 00:00:00', 'invalid-format');
    }
}
