<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\EventListener;

use EMS\ClientHelperBundle\Helper\InlineEdit\InlineEditHelper;
use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class InlineEditListener implements EventSubscriberInterface
{
    public function __construct(private InlineEditHelper $inlineEditHelper)
    {
    }

    /**
     * @return array<mixed>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -128],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = EmschRequest::fromRequest($event->getRequest());
        $response = $event->getResponse();

        if (!$this->isHtmlRequestResponse($request, $response) || $request->isInlineEditor()) {
            return;
        }

        if ($request->isInlineEditorEnabled()) {
            $this->injectInlineEditorButton($request, $response);
        }
    }

    private function injectInlineEditorButton(EmschRequest $request, Response $response): void
    {
        if (false === $content = $response->getContent()) {
            return;
        }

        $headPos = \stripos($content, '</head>');
        if (false !== $headPos) {
            $content =
                \substr($content, 0, $headPos)
                ."\n".$this->inlineEditHelper->renderInjectHead()."\n"
                .\substr($content, $headPos);
        }

        $bodyPos = \strripos($content, '</body>');
        if (false !== $bodyPos) {
            $content =
                \substr($content, 0, $bodyPos)
                ."\n".$this->inlineEditHelper->renderInjectBody($request)."\n"
                .\substr($content, $bodyPos);
        }

        $response->setContent($content);
    }

    private function isHtmlRequestResponse(EmschRequest $request, Response $response): bool
    {
        if ('html' !== $request->getRequestFormat()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        return $contentType && \str_starts_with(\strtolower($contentType), 'text/html');
    }
}
