<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use EMS\ClientHelperBundle\Exception\TemplatingException;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestManager;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class ExceptionHelper
{
    public function __construct(private readonly Environment $twig, private readonly ClientRequestManager $manager, private readonly bool $enabled, private readonly bool $debug, private readonly string $template = '')
    {
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

        $code = $exception->getStatusCode();
        $template = $this->getTemplate($code);

        return new Response($this->twig->render($template, [
            'trans_default_domain' => $this->manager->getDefault()->getCacheKey(),
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code] ?? '',
            'exception' => $exception,
        ]));
    }

    private function getTemplate(int $code): string
    {
        $customCodeTemplate = \str_replace('{code}', \strval($code), $this->template);

        if ($this->templateExists($customCodeTemplate)) {
            return $customCodeTemplate;
        }

        $errorTemplate = \str_replace('{code}', '', $this->template);

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
