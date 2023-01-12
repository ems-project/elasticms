<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Html\Sanitizer;

use EMS\Helpers\Html\Sanitizer\HtmlSanitizerConfigBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class HtmlSanitizerConfigBuilderTest extends TestCase
{
    public function testUndefinedOptionShouldThrowError(): void
    {
        $this->expectException(UndefinedOptionsException::class);
        $this->factory(['test' => 'test']);
    }

    public function testAllowAttributes(): void
    {
        $this->assertArrayNotHasKey('data-test', $this->factory()->getAllowedElements()['div']);
        $this->assertArrayHasKey('data-test', $this->factory(['allow_attributes' => ['data-test' => 'div']])->getAllowedElements()['div']);
        $this->assertArrayHasKey('data-test', $this->factory(['allow_attributes' => ['data-test' => '*']])->getAllowedElements()['a']);
    }

    public function testAllowSafeElements(): void
    {
        $this->assertEmpty($this->factory(['allow_safe_elements' => false])->getAllowedElements());
    }

    public function testAllowLinksWithAllAttributes(): void
    {
        $elements = $this->factory([
            'allow_safe_elements' => false,
            'allow_elements' => ['a' => '*'],
        ])->getAllowedElements();

        $this->assertArrayHasKey('a', $elements);
        $this->assertNotEmpty($elements['a']);
    }

    public function testAllowDivWithClassAttribute(): void
    {
        $this->assertEquals(
            ['div' => ['class' => true]],
            $this->factory([
                'allow_safe_elements' => false,
                'allow_elements' => ['div' => ['class']],
            ])->getAllowedElements()
        );
    }

    public function testBlockElements(): void
    {
        $blockElements = $this->factory(['block_elements' => ['a', 'img', 'span']])->getBlockedElements();

        $this->assertEquals(['a', 'img', 'span'], \array_keys($blockElements));
    }

    public function testDropAttributes(): void
    {
        $this->assertArrayHasKey('title', $this->factory()->getAllowedElements()['a']);
        $this->assertArrayNotHasKey('title', $this->factory(['drop_attributes' => ['title' => 'a']])->getAllowedElements()['a']);
        $this->assertArrayNotHasKey('title', $this->factory(['drop_attributes' => ['title' => '*']])->getAllowedElements()['div']);
    }

    public function testDropElements(): void
    {
        $this->assertArrayHasKey('a', $this->factory()->getAllowedElements());
        $this->assertArrayNotHasKey('a', $this->factory(['drop_elements' => ['a']])->getAllowedElements());
    }

    private function factory(array $settings = []): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfigBuilder($settings))->build();
    }
}
