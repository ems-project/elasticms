<?php

declare(strict_types=1);

namespace EMS\Tests\CommonBundle\Unit\Controller;

use EMS\CommonBundle\Controller\FileController;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\CommonBundle\Twig\RequestRuntime;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FileControllerAiTest extends TestCase
{
    private const string TEST_IMAGE_PATH = __DIR__.'/fixtures/image.png';
    private FileController $controller;
    private Processor $processor;
    private RequestRuntime $requestRuntime;

    #[\Override]
    protected function setUp(): void
    {
        $this->processor = $this->createMock(Processor::class);
        $this->requestRuntime = $this->createMock(RequestRuntime::class);
        $this->controller = new FileController($this->processor, $this->requestRuntime);
    }

    public function testAsset(): void
    {
        $this->processor->expects($this->once())
            ->method('getResponse')
            ->willReturn(new Response());

        $response = $this->controller->asset(new Request(), 'hash', 'hash_config', 'filename');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testResolveAsset(): void
    {
        $this->processor->expects($this->once())
            ->method('resolveAndGetResponse')
            ->willReturn(new Response());

        $response = $this->controller->resolveAsset(new Request(), [], []);
        $this->assertInstanceOf(Response::class, $response);
    }

    #[IgnoreDeprecations]
    public function testView(): void
    {
        $this->requestRuntime->expects($this->once())
            ->method('assetPath')
            ->willReturn('/path/to/asset');

        $response = $this->controller->view(new Request(), 'sha1');
        $this->assertInstanceOf(Response::class, $response);
    }

    #[IgnoreDeprecations]
    public function testDownload(): void
    {
        $this->requestRuntime->expects($this->once())
            ->method('assetPath')
            ->willReturn('/path/to/asset');

        $response = $this->controller->download(new Request(), 'sha1');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGenerateLocalImage(): void
    {
        $resource = \fopen(self::TEST_IMAGE_PATH, 'r');
        $this->processor->expects($this->once())
            ->method('generateLocalImage')
            ->willReturn(new Stream($resource));

        $response = $this->controller->generateLocalImage(new Request(), 'filename');
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCloseSessionWithoutSession(): void
    {
        $request = new Request();
        $this->invokeMethod($this->controller, 'closeSession', [$request]);
        $this->assertFalse($request->hasSession());
    }

    public function testCloseSessionWithStartedSession(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('isStarted')->willReturn(true);
        $session->expects($this->once())->method('save');

        $request = new Request();
        $request->setSession($session);

        $this->invokeMethod($this->controller, 'closeSession', [$request]);
        $this->assertTrue($request->hasSession());
    }

    public function testGetFile(): void
    {
        $this->requestRuntime->expects($this->once())
            ->method('assetPath')
            ->willReturn('/path/to/asset');

        $request = new Request();
        $response = $this->invokeMethod($this->controller, 'getFile', [$request, 'sha1', ResponseHeaderBag::DISPOSITION_ATTACHMENT]);
        $this->assertInstanceOf(Response::class, $response);
    }

    private function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($object::class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }
}
