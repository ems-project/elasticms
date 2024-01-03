<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Exception;

use EMS\CommonBundle\Exception\DateTimeCreationException;
use PHPUnit\Framework\TestCase;

class DateTimeCreationExceptionAiTest extends TestCase
{
    public function testFromArrayWithValidKey(): void
    {
        $data = ['date' => 'invalid-date-format'];
        $exception = DateTimeCreationException::fromArray($data, 'date');

        $this->assertInstanceOf(DateTimeCreationException::class, $exception);
        $this->assertEquals('Could not create a DateTime from input value: "invalid-date-format", with key: "date"', $exception->getMessage());
    }

    public function testFromArrayWithInvalidKey(): void
    {
        $data = ['otherKey' => '2023-01-01'];
        $exception = DateTimeCreationException::fromArray($data, 'date');

        $this->assertInstanceOf(DateTimeCreationException::class, $exception);
        $this->assertEquals('Could not create a DateTime from input value: "[ERROR: key out of bound]", with key: "date"', $exception->getMessage());
    }

    public function testFromArrayWithPreviousException(): void
    {
        $data = ['date' => 'invalid-date-format'];
        $previous = new \Exception('Previous exception');
        $exception = DateTimeCreationException::fromArray($data, 'date', 0, $previous);

        $this->assertInstanceOf(DateTimeCreationException::class, $exception);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
