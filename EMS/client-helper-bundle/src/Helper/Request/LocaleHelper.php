<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\Request;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class LocaleHelper
{
    /**
     * @param string[] $locales
     */
    public function __construct(private array $locales)
    {
    }

    public function redirectMissingLocale(Request $request): RedirectResponse
    {
        $destination = $request->getPathInfo();

        if ('' === $destination || '/' === $destination) {
            $destination = null;
        }

        if ($request->cookies->has('_locale')) {
            $url = $request->getUriForPath('/'.$request->cookies->get('_locale').$destination);
        } elseif (1 === \count($this->locales)) {
            $url = $request->getUriForPath('/'.$this->locales[0].$destination);
        } else {
            $url = $request->getUriForPath('/'.$destination);
        }

        return new RedirectResponse($url);
    }

    public function getLocale(Request $request): string|false
    {
        $locale = $request->attributes->get('_locale', false);

        if ($locale) {
            return $locale;
        }

        $localeUri = $this->getLocaleFromUri($request->getPathInfo());

        if ($localeUri) {
            $request->setLocale($localeUri);

            return $localeUri;
        }

        return false;
    }

    private function getLocaleFromUri(string $uri): string|false
    {
        $regex = \sprintf('/^\/(?P<locale>%s).*$/', \implode('|', $this->locales));
        \preg_match($regex, $uri, $matches);

        return $matches['locale'] ?? false;
    }
}
