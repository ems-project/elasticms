<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\DateTime;
use PHPUnit\Framework\TestCase;

class DateTimeTest extends TestCase
{
    public function testCreate()
    {
        self::assertSame((new \DateTimeImmutable())->format('c'), DateTime::create('now')->format('c'));
    }

    public function testCreateFromFormat()
    {
        self::assertSame(\DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '1977-02-09T16:00:00+02:00')->format('c'), DateTime::createFromFormat('1977-02-09T16:00:00+02:00')->format('c'));

        $this->expectException(\RuntimeException::class);
        DateTime::createFromFormat('1977-02-09');
    }

    public function testCreate2(): void
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

    public function testCreateFromFormat2(): void
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
