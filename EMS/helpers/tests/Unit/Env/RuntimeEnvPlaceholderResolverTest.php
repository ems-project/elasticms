<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Env;

use EMS\Helpers\Env\RuntimeEnvPlaceholderResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuntimeEnvPlaceholderResolver::class)]
final class RuntimeEnvPlaceholderResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->unsetEnv('MY_AUTH_KEY');
        $this->unsetEnv('NUM');
        $this->unsetEnv('FLT');
        $this->unsetEnv('FLAG');
        $this->unsetEnv('WS');
        $this->unsetEnv('B64');
        $this->unsetEnv('JSON');
        $this->unsetEnv('URLVAL');
        $this->unsetEnv('FILEPATH');
        $this->unsetEnv('MISSING');
        $this->unsetEnv('A');
        $this->unsetEnv('B');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->setUp();
    }

    private function setEnv(string $key, string $value): void
    {
        \putenv(\sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private function unsetEnv(string $key): void
    {
        \putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }

    public function testResolvesSimpleStringProcessor(): void
    {
        $this->setEnv('MY_AUTH_KEY', 'abc123');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $out = $resolver->resolve('My auth key is %env(string:MY_AUTH_KEY)%.');

        self::assertSame('My auth key is abc123.', $out);
    }

    public function testResolvesSimpleSuperTrimProcessor(): void
    {
        $this->setEnv('A', " abc\n\t123 ");
        $resolver = new RuntimeEnvPlaceholderResolver();
        $out = $resolver->resolve('%env(super_trim:A)%.');

        self::assertSame('abc 123.', $out);
    }

    public function testResolvesMultiplePlaceholdersInOneString(): void
    {
        $this->setEnv('A', 'foo');
        $this->setEnv('B', 'bar');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $out = $resolver->resolve('X=%env(string:A)%, Y=%env(string:B)%');

        self::assertSame('X=foo, Y=bar', $out);
    }

    public function testIntAndFloatProcessors(): void
    {
        $this->setEnv('NUM', '42');
        $this->setEnv('FLT', '3.14');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $out = $resolver->resolve('n=%env(int:NUM)%, f=%env(float:FLT)%');

        self::assertSame('n=42, f=3.14', $out);
    }

    #[DataProvider('boolProvider')]
    public function testBoolProcessor(string $raw, string $expected): void
    {
        $this->setEnv('FLAG', $raw);
        $resolver = new RuntimeEnvPlaceholderResolver();

        $out = $resolver->resolve('flag=%env(bool:FLAG)%');

        self::assertSame('flag='.$expected, $out);
    }

    public static function boolProvider(): array
    {
        return [
            ['true',  '1'],
            ['TRUE',  '1'],
            ['yes',   '1'],
            ['on',    '1'],
            ['1',     '1'],
            ['false', ''],
            ['off',   ''],
            ['no',    ''],
            ['0',     ''],
            ['anything-else', ''],
        ];
    }

    public function testTrimProcessor(): void
    {
        $this->setEnv('WS', '  spaced  ');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $out = $resolver->resolve('v=[%env(trim:string:WS)%]');

        self::assertSame('v=[spaced]', $out);
    }

    public function testBase64AndJsonProcessors(): void
    {
        $payload = \json_encode(['token' => 'xyz', 'exp' => 1700000000], JSON_THROW_ON_ERROR);
        $this->setEnv('B64', \base64_encode($payload));
        $resolver = new RuntimeEnvPlaceholderResolver();
        $out = $resolver->resolve('data=%env(json:base64:B64)%');

        self::assertSame('data={"token":"xyz","exp":1700000000}', $out);
    }

    public function testInvalidBase64Throws(): void
    {
        $this->setEnv('B64', '***not-base64***');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid base64 ***not-base64***');
        $resolver->resolve('x=%env(base64:B64)%');
    }

    public function testInvalidJsonThrows(): void
    {
        $this->setEnv('JSON', '{invalid json]');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid json Syntax error');
        $resolver->resolve('x=%env(json:JSON)%');
    }

    public function testUrlencodeAndUrldecodeProcessors(): void
    {
        $this->setEnv('URLVAL', 'a b+c/d?e=f&g#h');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $encoded = $resolver->resolve('enc=%env(urlencode:URLVAL)%');
        self::assertSame('enc=a%20b%2Bc%2Fd%3Fe%3Df%26g%23h', $encoded);

        $this->setEnv('URLVAL', 'a%20b%2Bc%2Fd%3Fe%3Df%26g%23h');
        $decoded = $resolver->resolve('dec=%env(urldecode:URLVAL)%');
        self::assertSame('dec=a b+c/d?e=f&g#h', $decoded);
    }

    public function testFileProcessorReadsFileContent(): void
    {
        $tmp = \tempnam(\sys_get_temp_dir(), 'envtest_');
        self::assertIsString($tmp);
        \file_put_contents($tmp, 'secret');
        $this->setEnv('FILEPATH', $tmp);

        try {
            $resolver = new RuntimeEnvPlaceholderResolver();
            $out = $resolver->resolve('k=%env(file:FILEPATH)%');
            self::assertSame('k=secret', $out);
        } finally {
            @\unlink($tmp);
        }
    }

    public function testFileProcessorThrowsOnMissingFile(): void
    {
        $this->setEnv('FILEPATH', '/path/does/not/exist.txt');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File "/path/does/not/exist.txt" does not exit');
        $resolver->resolve('x=%env(file:FILEPATH)%');
    }

    public function testMissingEnvThrowsInStrictMode(): void
    {
        $resolver = new RuntimeEnvPlaceholderResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('[env:MISSING] Environment variable not found');
        $resolver->resolve('x=%env(string:MISSING)%');
    }

    public function testMissingEnvIsKeptInTolerantMode(): void
    {
        $resolver = new RuntimeEnvPlaceholderResolver(false);
        $out = $resolver->resolve('x=%env(string:MISSING)%');

        self::assertSame('x=%env(MISSING)%', $out);
    }

    public function testUnknownProcessorThrows(): void
    {
        $this->setEnv('MY_AUTH_KEY', 'abc123');
        $resolver = new RuntimeEnvPlaceholderResolver();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown env processor');
        $resolver->resolve('x=%env(foobar:MY_AUTH_KEY)%');
    }
}
