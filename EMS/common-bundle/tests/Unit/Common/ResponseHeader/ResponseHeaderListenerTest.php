<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\ResponseHeader;

use EMS\CommonBundle\Common\ResponseHeader\ResponseHeaderManager;
use EMS\CommonBundle\EventListener\ResponseHeaderListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ResponseHeaderListenerTest extends TestCase
{
    public function testOnKernelResponseAddsContextHeadersToResponse(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $context = (new ResponseHeaderManager($requestStack))->getContext();
        $context->setHeader('x-request-id', 'request-123');

        $response = new Response();
        $this->createListener($requestStack)->onKernelResponse($this->createResponseEvent($response));

        self::assertSame('request-123', $response->headers->get('X-Request-Id'));
        self::assertNull($response->headers->get('Content-Security-Policy'));
    }

    public function testOnKernelResponseAddsGeneratedCspHeaderToResponse(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/'));
        $context = (new ResponseHeaderManager($requestStack))->getContext();
        $context->addCspSource('default-src', "'self'");
        $context->addCspSource('script-src', 'https://cdn.example.com');

        $response = new Response();
        $this->createListener($requestStack)->onKernelResponse($this->createResponseEvent($response));

        self::assertSame(
            "default-src 'self'; script-src https://cdn.example.com",
            $response->headers->get('Content-Security-Policy'),
        );
    }

    private function createListener(RequestStack $requestStack): ResponseHeaderListener
    {
        return new ResponseHeaderListener(new ResponseHeaderManager($requestStack));
    }

    private function createResponseEvent(Response $response): ResponseEvent
    {
        return new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/'),
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );
    }
}
