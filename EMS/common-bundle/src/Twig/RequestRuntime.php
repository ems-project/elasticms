<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Helper\EmsFields;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class RequestRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly RequestStack $requestStack, private readonly AssetRuntime $assetRuntime)
    {
    }

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
     *
     * @return mixed
     */
    public function localeAttribute(array $source, string $attribute)
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
    public function checkIp(string $requestIp, string|array $ipsOrSubnets): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->isMethodSafe()) {
            throw new \RuntimeException(\sprintf('The safe method %s is not allowed with ems_check_ip()', $request->getMethod()));
        }

        return IpUtils::checkIp($requestIp, $ipsOrSubnets);
    }

    #[\Deprecated(message: 'assetAverageColor will be removed in elasticms/common-bundle 1.20. Use AssetRuntime::assetAverageColor instead.')]
    public function assetAverageColor(string $hash): string
    {
        \trigger_error('assetAverageColor will be removed in elasticms/common-bundle 1.20. Use AssetRuntime::assetAverageColor instead.', E_USER_DEPRECATED);

        return $this->assetRuntime->assetAverageColor($hash);
    }
}
