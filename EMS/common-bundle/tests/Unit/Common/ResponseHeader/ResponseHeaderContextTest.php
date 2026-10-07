<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\ResponseHeader;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderContext;
use PHPUnit\Framework\TestCase;

final class ResponseHeaderContextTest extends TestCase
{
    public function testSetHeaderNormalizesAndOverwritesHeader(): void
    {
        $context = new ResponseHeaderContext();

        $context->setHeader('content-type', 'text/plain');
        $context->setHeader('CONTENT-TYPE', 'text/html');

        self::assertSame(['Content-Type' => 'text/html'], $context->getHeaders());
    }

    public function testSetHeaderRejectsInvalidValue(): void
    {
        $context = new ResponseHeaderContext();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for HTTP header "X-Test".');

        $context->setHeader('x-test', "value\r\nInjected: true");
    }

    public function testSetHeaderRejectsContentSecurityPolicyHeaders(): void
    {
        $context = new ResponseHeaderContext();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Use addCspSource() to configure the Content Security Policy.');

        $context->setHeader('content-security-policy', "default-src 'none'");
    }

    public function testAddCspSourceNormalizesDirectivesAndIgnoresDuplicates(): void
    {
        $context = new ResponseHeaderContext();

        $context->addCspSource('SCRIPT-SRC', "'self'");
        $context->addCspSource('script-src', "'self'");
        $context->addCspSource('script-src', 'https://cdn.example.com');

        self::assertSame(['script-src' => ["'self'", 'https://cdn.example.com']], $context->getCspSources());
        self::assertSame("script-src 'self' https://cdn.example.com", $context->getCspHeader());
    }

    public function testCspHeaderIsNullWhenNoSourceWasAdded(): void
    {
        $context = new ResponseHeaderContext();

        self::assertNull($context->getCspHeader());
    }

    public function testNoneCannotBeCombinedWithOtherSources(): void
    {
        $context = new ResponseHeaderContext();
        $context->addCspSource('default-src', "'none'");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("CSP directive \"default-src\" cannot combine 'none' with other sources.");

        $context->addCspSource('default-src', 'https://example.com');
    }

    public function testNoneCannotBeAddedAfterAnotherSource(): void
    {
        $context = new ResponseHeaderContext();
        $context->addCspSource('default-src', 'https://example.com');

        $this->expectException(\InvalidArgumentException::class);

        $context->addCspSource('default-src', "'none'");
    }

    public function testAddCspSourceRejectsInvalidDirective(): void
    {
        $context = new ResponseHeaderContext();

        $this->expectException(\InvalidArgumentException::class);

        $context->addCspSource('script_src', "'self'");
    }

    public function testNonceIsGeneratedLazilyAndIsStable(): void
    {
        $context = new ResponseHeaderContext();

        self::assertFalse($context->hasNonce());

        $nonce = $context->getNonce();

        self::assertTrue($context->hasNonce());
        self::assertSame($nonce, $context->getNonce());
        self::assertSame(32, \strlen(\base64_decode($nonce, true)));
    }
}
