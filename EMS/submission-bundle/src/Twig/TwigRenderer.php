<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use EMS\FormBundle\Submission\HandleRequestInterface;
use Twig\Environment;

final class TwigRenderer
{
    private Environment $templating;

    public function __construct(Environment $templating)
    {
        $this->templating = $templating;
    }

    public function renderEndpoint(HandleRequestInterface $handleRequest): string
    {
        return $this->renderTemplate($handleRequest->getEndPoint(), $this->getContext($handleRequest));
    }

    /**
     * @return array<string, mixed>
     */
    public function renderEndpointJSON(HandleRequestInterface $handleRequest): array
    {
        return $this->jsonDecode($this->renderEndpoint($handleRequest));
    }

    public function renderMessage(HandleRequestInterface $handleRequest): string
    {
        return $this->renderTemplate($handleRequest->getMessage(), $this->getContext($handleRequest));
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderMessageBlock(HandleRequestInterface $handleRequest, string $blockName, array $context = []): ?string
    {
        $template = $this->templating->createTemplate($handleRequest->getMessage());

        if (!$template->hasBlock($blockName)) {
            return null;
        }

        $context = \array_merge($context, $this->getContext($handleRequest));

        return $template->renderBlock($blockName, $context);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function renderMessageBlockJSON(HandleRequestInterface $handleRequest, string $blockName, array $context = []): array
    {
        return $this->jsonDecode($this->renderMessageBlock($handleRequest, $blockName, $context));
    }

    /**
     * @return array<string, mixed>
     */
    public function renderMessageJSON(HandleRequestInterface $handleRequest): array
    {
        return $this->jsonDecode($this->renderMessage($handleRequest));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderTemplate(string $template, array $context): string
    {
        return $this->templating->createTemplate($template)->render($context);
    }

    /**
     * @return array<string, mixed>
     */
    private function getContext(HandleRequestInterface $handleRequest): array
    {
        return [
            'config' => $handleRequest->getFormConfig(),
            'data' => $handleRequest->getFormData()->raw(),
            'formData' => $handleRequest->getFormData(),
            'request' => $handleRequest,
            'responses' => $handleRequest->getResponses(),
        ];
    }

    /**
     * @return array<mixed>
     */
    private function jsonDecode(?string $json): array
    {
        if (null === $json || '' === $json) {
            return [];
        }

        $decodedJson = \json_decode($json, true);

        if (JSON_ERROR_NONE !== \json_last_error()) {
            throw new \InvalidArgumentException('invalid json!');
        }

        return $decodedJson;
    }
}
