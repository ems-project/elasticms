<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Date;

use EMS\Helpers\Date\DateTimeConvertor;
use PHPUnit\Framework\TestCase;

final class DateTimeConvertorTest extends TestCase
{
    public function testToDateTimeImmutableWithNull(): void
    {
        $this->assertNull(DateTimeConvertor::toDateTimeImmutable(null));
    }

    public function testToDateTimeImmutableWithDateTimeImmutable(): void
    {
        $date = new \DateTimeImmutable('2026-04-14 19:10:59', new \DateTimeZone('UTC'));

        $this->assertSame($date, DateTimeConvertor::toDateTimeImmutable($date));
    }

    public function testToDateTimeImmutableWithDateTime(): void
    {
        $date = new \DateTime('2026-04-14 19:10:59', new \DateTimeZone('UTC'));
        $converted = DateTimeConvertor::toDateTimeImmutable($date);

        $this->assertInstanceOf(\DateTimeImmutable::class, $converted);
        $this->assertSame('2026-04-14 19:10:59', $converted?->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $converted?->getTimezone()->getName());
    }

    public function testToDateTimeImmutableWithString(): void
    {
        $converted = DateTimeConvertor::toDateTimeImmutable('2026-04-14 19:10:59');

        $this->assertInstanceOf(\DateTimeImmutable::class, $converted);
        $this->assertSame('2026-04-14 19:10:59', $converted?->format('Y-m-d H:i:s'));
    }

    public function testToDateTimeImmutableWithArrayPayload(): void
    {
        $converted = DateTimeConvertor::toDateTimeImmutable([
            'date' => '2026-04-14 19:10:59.000000',
            'timezone_type' => 3,
            'timezone' => 'UTC',
        ]);

        $this->assertInstanceOf(\DateTimeImmutable::class, $converted);
        $this->assertSame('2026-04-14 19:10:59', $converted?->format('Y-m-d H:i:s'));
    }

    public function testToDateTimeImmutableWithInvalidValue(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot convert value of type "integer".');

        DateTimeConvertor::toDateTimeImmutable(123);
    }
}
