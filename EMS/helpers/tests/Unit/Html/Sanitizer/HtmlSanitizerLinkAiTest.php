<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Html\Sanitizer;

use EMS\Helpers\Html\Sanitizer\HtmlSanitizerLink;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizerLinkAiTest extends TestCase
{
    private HtmlSanitizerLink $sanitizer;

    #[\Override]
    protected function setUp(): void
    {
        $this->sanitizer = new HtmlSanitizerLink();
    }

    public function testSanitizeAttributeWithEmsScheme()
    {
        $element = 'a';
        $attribute = 'href';
        $value = 'ems://some-resource';
        $config = new HtmlSanitizerConfig();

        $sanitizedValue = $this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config);
        $this->assertEquals('ems://some-resource', $sanitizedValue);
    }

    public function testSanitizeAttributeWithHttpScheme()
    {
        $element = 'a';
        $attribute = 'href';
        $value = 'http://example.com';
        $config = new HtmlSanitizerConfig();

        $sanitizedValue = $this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config);
        $this->assertEquals('http://example.com', $sanitizedValue);
    }

    public function testSanitizeAttributeWithJavascriptScheme()
    {
        $element = 'a';
        $attribute = 'href';
        $value = 'javascript:alert(1)';
        $config = new HtmlSanitizerConfig();

        $sanitizedValue = $this->sanitizer->sanitizeAttribute($element, $attribute, $value, $config);
        $this->assertNull($sanitizedValue);
    }

    public function testGetSupportedElements()
    {
        $this->assertNull($this->sanitizer->getSupportedElements());
    }

    public function testGetSupportedAttributes()
    {
        $this->assertEquals(['src', 'href', 'lowsrc', 'background', 'ping'], $this->sanitizer->getSupportedAttributes());
    }
}
