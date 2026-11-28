<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\File;

use EMS\Helpers\File\Path;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    #[DataProvider('absolutePathsProvider')]
    public function testIsAbsolutePath(string $path, bool $expected): void
    {
        self::assertSame($expected, Path::isAbsolutePath($path));
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function absolutePathsProvider(): iterable
    {
        yield ['/tmp/project', true];
        yield ['C:/temp/project', true];
        yield ['C:\\temp\\project', true];
        yield ['\\server\share', true];
        yield ['relative/path', false];
        yield ['./relative/path', false];
        yield ['', false];
    }

    #[DataProvider('relativePathsProvider')]
    public function testIsRelativePath(string $path, bool $expected): void
    {
        self::assertSame($expected, Path::isRelativePath($path));
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function relativePathsProvider(): iterable
    {
        yield ['relative/path', true];
        yield ['./relative/path', true];
        yield ['../relative/path', true];
        yield ['/tmp/project', false];
        yield ['C:\\temp\\project', false];
        yield ['', false];
    }

    #[DataProvider('joinProvider')]
    public function testJoin(array $parts, string $expected): void
    {
        self::assertSame($expected, Path::join(...$parts));
    }

    /**
     * @return iterable<array{array<string>, string}>
     */
    public static function joinProvider(): iterable
    {
        yield [['foo', 'bar', 'baz'], 'foo/bar/baz'];
        yield [['/tmp', 'project', 'src'], '/tmp/project/src'];
        yield [['/tmp/', '/project/', '/src/'], '/tmp/project/src'];
        yield [['C:\\temp', 'project', 'src'], 'C:/temp/project/src'];
        yield [['', 'foo', '', 'bar'], 'foo/bar'];
        yield [['/'], '/'];
        yield [[], ''];
    }
}
