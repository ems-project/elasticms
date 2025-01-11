<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Html\Sanitizer;

use EMS\Helpers\Html\Sanitizer\HtmlSanitizerClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerClassAiTest extends TestCase
{
    private HtmlSanitizerClass $sanitizer;

    #[\Override]
    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizerClass([
            'allow' => ['allowed-class', 'new-class'],
            'drop' => ['dropped-class'],
            'replace' => ['old-class' => 'new-class'],
        ]);
    }

    public function testGetSupportedElements(): void
    {
        $this->assertNull($this->sanitizer->getSupportedElements());
    }

    public function testGetSupportedAttributes(): void
    {
        $expectedAttributes = ['class'];
        $this->assertEquals($expectedAttributes, $this->sanitizer->getSupportedAttributes());
    }

    public function testSanitizeAttribute(): void
    {
        $config = $this->createMock(HtmlSanitizerConfig::class);
        $element = 'div';
        $attribute = 'class';

        // Test allow
        $value = 'allowed-class';
        $this->assertEquals('allowed-class', $this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config));

        // Test drop
        $value = 'dropped-class';
        $this->assertNull($this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config));

        // Test replace
        $value = 'old-class';
        $this->assertEquals('new-class', $this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config));

        // Test multiple classes
        $value = 'allowed-class dropped-class old-class';
        $this->assertEquals('allowed-class new-class', $this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config));
    }
}
