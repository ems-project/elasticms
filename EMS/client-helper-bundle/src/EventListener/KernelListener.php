<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\EventListener;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\ClientHelperBundle\Helper\Request\ExceptionHelper;
use EMS\ClientHelperBundle\Helper\Request\LocaleHelper;
use EMS\ClientHelperBundle\Helper\Translation\Translator;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class KernelListener implements EventSubscriberInterface
{
    public function __construct(private EnvironmentHelper $environmentHelper, private Translator $translationHelper, private LocaleHelper $localeHelper, private ExceptionHelper $exceptionHelper, private bool $bindLocale)
    {
    }

    /**
     * @return array<string, array<mixed>>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['bindEnvironment', 100],
                ['loadTranslations', 11],
            ],
            KernelEvents::RESPONSE => [
                ['bindLocale', 17],
            ],
            KernelEvents::EXCEPTION => [
                ['bindEnvironment', 100],
                ['redirectMissingLocale', 21],
                ['loadTranslations', 20], // not found is maybe redirected or custom error pages with translations
                ['customErrorTemplate', -10],
            ],
        ];
    }

    public function bindEnvironment(KernelEvent $event): void
    {
        $request = $event->getRequest();

        foreach ($this->environmentHelper->getEnvironments() as $environment) {
            if ($environment->matchRequest($request)) {
                $environment->makeActive();
                $environment->modifyRequest($request);
                break;
            }
        }
    }

    public function loadTranslations(KernelEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->translationHelper->addCatalogues();
        }
    }

    public function bindLocale(ResponseEvent $event): void
    {
        if ($this->bindLocale && $locale = $this->localeHelper->getLocale($event->getRequest())) {
            $event->getResponse()->headers->setCookie(new Cookie('_locale', $locale, \strtotime('now + 12 months')));
        }
    }

    public function redirectMissingLocale(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        if (!$this->bindLocale || !$event->isMainRequest() || !$exception instanceof NotFoundHttpException) {
            return;
        }

        $route = $request->attributes->get('_route', null);

        if (null === $route || \preg_match('/(emsch_api_).*/', \strval($route))) {
            return;
        }

        if (false === $this->localeHelper->getLocale($request)) {
            $event->setResponse($this->localeHelper->redirectMissingLocale($request));
        }
    }

    public function customErrorTemplate(ExceptionEvent $event): void
    {
        if (!$this->exceptionHelper->isEnabled()) {
            return;
        }

        $flattenException = FlattenException::createFromThrowable($event->getThrowable());

        if ($template = $this->exceptionHelper->renderError($flattenException)) {
            $event->setResponse($template);
        }
    }
}
