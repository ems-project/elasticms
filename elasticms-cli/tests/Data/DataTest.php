<?php

declare(strict_types=1);

namespace App\CLI\Tests\Data;

use App\CLI\Client\Data\Data;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataTest extends TestCase
{
    #[DataProvider('provideTestData')]
    public function testCount(array $testData): void
    {
        $this->assertCount(10, $testData);
    }

    #[DataProvider('provideTestData')]
    public function testSearchAndReplace(array $testData): void
    {
        $testData = new Data($testData);
        $testData->searchAndReplace(2, 'Test page description', 'Replaced test');
        foreach ($testData as $row) {
            $this->assertSame('Replaced test', $row[2]);
        }
    }

    #[DataProvider('provideTestData')]
    public function testSliceFirst3(array $testData): void
    {
        $testData = new Data($testData);
        $testData->slice(null, 3);
        $this->assertCount(3, $testData);

        foreach ($testData as $i => $row) {
            $this->assertSame(++$i, $row[0]);
        }
    }

    #[DataProvider('provideTestData')]
    public function testSliceLast8(array $testData): void
    {
        $testData = new Data($testData);
        $testData->slice(2);
        $this->assertCount(8, $testData);

        foreach ($testData as $i => $row) {
            $this->assertSame(++$i + 2, $row[0]);
        }
    }

    #[DataProvider('provideTestData')]
    public function testSliceFrom4Until6(array $testData): void
    {
        $testData = new Data($testData);
        $testData->slice(3, 3);
        $this->assertCount(3, $testData);

        foreach ($testData as $i => $row) {
            $this->assertSame(++$i + 3, $row[0]);
        }
    }

    public static function provideTestData(): array
    {
        return [
            'exampleDataPages' => [
                'testData' => [
                    0 => [1, 'Test page 1', 'Test page description'],
                    1 => [2, 'Test page 2', 'Test page description'],
                    2 => [3, 'Test page 3', 'Test page description'],
                    3 => [4, 'Test page 4', 'Test page description'],
                    4 => [5, 'Test page 5', 'Test page description'],
                    5 => [6, 'Test page 6', 'Test page description'],
                    6 => [7, 'Test page 7', 'Test page description'],
                    7 => [8, 'Test page 8', 'Test page description'],
                    8 => [9, 'Test page 9', 'Test page description'],
                    9 => [10, 'Test page 10', 'Test page description'],
                ],
            ],
        ];
    }
}
