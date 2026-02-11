<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

readonly class RequestExtension
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[AsTwigFunction(name: 'ems_flash')]
    public function flash(string $type, string $message): void
    {
        $session = $this->requestStack->getSession();

        if (!$session instanceof FlashBagAwareSessionInterface) {
            return;
        }

        $session->getFlashBag()->add($type, $message);
    }

    /**
     * @param array<mixed> $source
     */
    #[AsTwigFilter(name: 'ems_locale_attr')]
    public function localeAttribute(array $source, string $attribute): mixed
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return '';
        }

        $locale = $request->getLocale();

        return $source[$attribute.$locale] ?? '';
    }

    /**
     * @param string[]|string $ipsOrSubnets
     */
    #[AsTwigFunction(name: 'ems_check_ip')]
    public function checkIp(string $requestIp, string|array $ipsOrSubnets): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed with ems_check_ip()', $request->getMethod()));
        }

        return IpUtils::checkIp($requestIp, $ipsOrSubnets);
    }
}
