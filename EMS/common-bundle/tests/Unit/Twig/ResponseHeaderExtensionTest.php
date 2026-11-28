<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Twig;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderContext;
use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderManager;
use EMS\CommonBundle\Twig\ResponseHeaderExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ResponseHeaderExtensionTest extends TestCase
{
    public function testRemoveHttpHeaderRemovesDefaultAndConfiguredHeaders(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $extension = new ResponseHeaderExtension(new ResponseHeaderManager($requestStack));
        $extension->httpHeader('X-Test', 'value');

        $extension->removeHttpHeader('x-content-type-options');
        $extension->removeHttpHeader('x-test');

        $context = $this->getContext($requestStack);
        self::assertArrayNotHasKey('X-Content-Type-Options', $context->getHeaders());
        self::assertArrayNotHasKey('X-Test', $context->getHeaders());
        self::assertArrayHasKey('Referrer-Policy', $context->getHeaders());
    }

    public function testClearHttpHeadersRemovesAllRegularHeadersButKeepsCspConfiguration(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $manager = new ResponseHeaderManager($requestStack);
        $extension = new ResponseHeaderExtension($manager);
        $extension->httpHeader('X-Test', 'value');
        $extension->cspSource('script-src', "'self'");

        $extension->clearHttpHeaders();

        self::assertSame([], $manager->getContext()->getHeaders());
        self::assertStringContainsString("script-src 'self'", $manager->getContext()->getCspHeader());
    }

    public function testCspScriptReturnsScriptTagAndAddsHashToCsp(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $manager = new ResponseHeaderManager($requestStack);
        $extension = new ResponseHeaderExtension($manager);
        $content = "console.log('Hello');";

        self::assertSame(
            '<script type="application/javascript">'.$content.'</script>',
            $extension->cspScriptHash($content, ['type' => 'application/javascript']),
        );

        $source = "'sha256-".\base64_encode(\hash('sha256', $content, true))."'";
        self::assertSame(['script-src' => [$source]], $manager->getContext()->getCspSources());
    }

    public function testCspStyleNormalizesLineEndingsBeforeHashing(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $manager = new ResponseHeaderManager($requestStack);
        $extension = new ResponseHeaderExtension($manager);
        $content = "body {\r\n    color: red;\r\n}";
        $normalizedContent = "body {\n    color: red;\n}";

        self::assertSame(
            '<style>'.$normalizedContent.'</style>',
            $extension->cspStyleHash($content),
        );

        $source = "'sha256-".\base64_encode(\hash('sha256', $normalizedContent, true))."'";
        self::assertSame(['style-src' => [$source]], $manager->getContext()->getCspSources());
    }

    public function testCspFiltersRenderSupportedAttributes(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $extension = new ResponseHeaderExtension(new ResponseHeaderManager($requestStack));

        self::assertSame(
            '<style id="main-style" media="screen" data-test="value" disabled>body { color: red; }</style>',
            $extension->cspStyleHash('body { color: red; }', [
                'id' => 'main-style',
                'media' => 'screen',
                'data-test' => 'value',
                'disabled' => true,
                'empty' => false,
            ]),
        );
    }

    /**
     * @dataProvider invalidCspAttributeProvider
     */
    #[DataProvider('invalidCspAttributeProvider')]
    public function testCspFiltersRejectUnsupportedAttributes(array $attributes): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $extension = new ResponseHeaderExtension(new ResponseHeaderManager($requestStack));

        $this->expectException(\InvalidArgumentException::class);
        $extension->cspScriptHash("console.log('Hello');", $attributes);
    }

    /**
     * @return iterable<string, array{array<string, string|bool|null>}>
     */
    public static function invalidCspAttributeProvider(): iterable
    {
        yield 'event handler' => [['onclick' => 'alert(1)']];
        yield 'external source' => [['src' => '/script.js']];
        yield 'nonce' => [['nonce' => 'nonce-value']];
        yield 'integrity' => [['integrity' => 'sha256-value']];
        yield 'invalid name' => [['data test' => 'value']];
    }

    /**
     * @dataProvider invalidCspContentProvider
     */
    #[DataProvider('invalidCspContentProvider')]
    public function testCspFiltersRejectClosingTags(string $filter, string $content): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $extension = new ResponseHeaderExtension(new ResponseHeaderManager($requestStack));

        $this->expectException(\InvalidArgumentException::class);
        $extension->{$filter}($content);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidCspContentProvider(): iterable
    {
        yield 'script closing tag' => ['cspScriptHash', 'console.log("</SCRIPT>");'];
        yield 'style closing tag' => ['cspStyleHash', '/* </style> */'];
    }

    private function getContext(RequestStack $requestStack): ResponseHeaderContext
    {
        return new ResponseHeaderManager($requestStack)->getContext();
    }
}
