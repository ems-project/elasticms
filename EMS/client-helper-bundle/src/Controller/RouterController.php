<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Cache\CacheHelper;
use EMS\ClientHelperBundle\Helper\Request\ExceptionHelper;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Helper\MimeTypeHelper;
use EMS\CommonBundle\Storage\Processor\Processor;
use EMS\Helpers\Standard\Json;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Twig\Error\RuntimeError;

final readonly class RouterController
{
    public function __construct(
        private Handler $handler,
        private Environment $templating,
        private Processor $processor,
        private CacheHelper $cacheHelper,
        private ExceptionHelper $exceptionHelper,
        private HttpKernel $httpKernel)
    {
    }

    public function handle(Request $request): Response
    {
        $result = $this->handler->handle($request);

        try {
            $response = new Response($this->templating->render($result['template'], $result['context']));
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }
        $this->cacheHelper->makeResponseCacheable($request, $response);

        return $response;
    }

    public function redirect(Request $request): Response
    {
        $result = $this->handler->handle($request);
        try {
            $json = $this->templating->render($result['template'], $result['context']);
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }
        $data = Json::decode($json);

        if (isset($data['controller'])) {
            $path = $data['path'] ?? [];
            $query = $data['query'] ?? [];
            $path['_controller'] = $data['controller'];
            $subRequest = $request->duplicate($query, null, $path);

            return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        if (isset($data['path'])) {
            return new BinaryFileResponse($data['path'], $data['status'] ?? 200, $data['headers'] ?? []);
        }

        if (!isset($data['url'])) {
            throw new HttpException($data['message'] ?? 'Page not found');
        }

        return new RedirectResponse($data['url'], $data['status'] ?? 302, $data['headers'] ?? []);
    }

    public function asset(Request $request): Response
    {
        $result = $this->handler->handle($request);
        try {
            $json = $this->templating->render($result['template'], $result['context']);
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }

        $data = Json::decode($json);

        if (\is_string($data['config'] ?? false)) {
            $response = $this->processor->getResponse($request, $data['hash'], $data['config'], $data['filename'], $data['immutable'] ?? false);
        } else {
            $config = $this->processor->configFactory($data['hash'], $data['config'] ?? []);
            $response = $this->processor->getStreamedResponse($request, $config, $data['filename'], $data['immutable'] ?? false);
        }

        $headers = $data['headers'] ?? null;
        if (\is_array($headers)) {
            $this->applyHeaders($response, $headers);
        }

        return $response;
    }

    public function makeResponse(Request $request): Response
    {
        $result = $this->handler->handle($request);
        try {
            $json = $this->templating->render($result['template'], $result['context']);
        } catch (RuntimeError $e) {
            throw $e->getPrevious() instanceof HttpException ? $e->getPrevious() : $e;
        }

        $data = Json::decode($json);
        if (!\is_string($data['content'] ?? null)) {
            throw new \RuntimeException('JSON requires at least a content field as a string');
        }

        $response = new Response();
        $response->setContent($data['content']);

        $headers = $data['headers'] ?? ['Content-Type' => MimeTypeHelper::TEXT_PLAIN];
        if (!\is_array($headers)) {
            throw new \RuntimeException('Unexpected non-array headers parameter');
        }

        $this->applyHeaders($response, $headers);

        return $response;
    }

    /**
     * @param array<string, string> $headers
     */
    private function applyHeaders(Response $response, array $headers): void
    {
        foreach ($headers as $key => $value) {
            $response->headers->add([$key => $value]);
        }
    }

    public function errorPreview(int $statusCode): Response
    {
        $exception = new HttpException($statusCode, 'This is a sample exception.');
        $flattenException = FlattenException::create($exception, $statusCode);

        return $this->exceptionHelper->generateResponse($flattenException);
    }
}
