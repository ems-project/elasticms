<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use EMS\ClientHelperBundle\Exception\TemplatingException;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class ExceptionHelper
{
    public function __construct(
        private Environment $twig,
        private ClientRequestManager $manager,
        private RequestStack $requestStack,
        private bool $enabled,
        private bool $debug,
        private string $template = ''
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function renderError(FlattenException $exception): Response|false
    {
        if ('' === $this->template || $this->debug) {
            return false;
        }

        return $this->generateResponse($exception);
    }

    public function generateResponse(FlattenException $exception): Response
    {
        $code = $exception->getStatusCode();
        $format = 'html';
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $format = $request->getRequestFormat() ?? 'html';
        }
        $template = $this->getTemplate($code, $format);

        return new Response($this->twig->render($template, [
            'trans_default_domain' => $this->manager->getDefault()->getCacheKey(),
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code] ?? '',
            'exception' => $exception,
        ]));
    }

    private function getTemplate(int $code, string $format): string
    {
        $customCodeTemplate = \str_replace([
            '{code}',
            '{_format}',
        ], [
            (string) $code,
            $format,
        ], $this->template);

        if ($this->templateExists($customCodeTemplate)) {
            return $customCodeTemplate;
        }

        $errorTemplate = \str_replace([
            '{code}',
            '{_format}',
        ], [
            '',
            $format,
        ], $this->template);

        if ($this->templateExists($errorTemplate)) {
            return $errorTemplate;
        }

        throw new TemplatingException(\sprintf('template "%s" does not exists', $errorTemplate));
    }

    private function templateExists(string $template): bool
    {
        try {
            $loader = $this->twig->getLoader();
            $loader->getSourceContext($template)->getCode();

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
