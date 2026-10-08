<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Twig;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderContext;
use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderManager;
use EMS\CommonBundle\Twig\ResponseHeaderExtension;
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

    private function getContext(RequestStack $requestStack): ResponseHeaderContext
    {
        return new ResponseHeaderManager($requestStack)->getContext();
    }
}
