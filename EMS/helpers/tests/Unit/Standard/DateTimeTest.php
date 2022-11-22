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
}
