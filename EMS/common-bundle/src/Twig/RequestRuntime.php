<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\CommonBundle\Helper\EmsFields;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class RequestRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly RequestStack $requestStack, private readonly AssetRuntime $assetRuntime)
    {
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
     * @deprecated assetPath will be removed in elasticms/common-bundle 1.20. Use AssetRuntime::assetPath instead.
     *
     * @param array<string, mixed> $fileField
     * @param array<string, mixed> $assetConfig
     */
    public function assetPath(array $fileField, array $assetConfig = [], string $route = 'ems_asset', string $fileHashField = EmsFields::CONTENT_FILE_HASH_FIELD, string $filenameField = EmsFields::CONTENT_FILE_NAME_FIELD, string $mimeTypeField = EmsFields::CONTENT_MIME_TYPE_FIELD, int $referenceType = UrlGeneratorInterface::RELATIVE_PATH): string
    {
        \trigger_error('assetPath will be removed in elasticms/common-bundle 1.20. Use AssetRuntime::assetPath instead.', E_USER_DEPRECATED);

        return $this->assetRuntime->assetPath($fileField, $assetConfig, $route, $fileHashField, $filenameField, $mimeTypeField, $referenceType);
    }

    /**
     * @deprecated assetAverageColor will be removed in elasticms/common-bundle 1.20. Use AssetRuntime::assetAverageColor instead.
     */
    public function assetAverageColor(string $hash): string
    {
        \trigger_error('assetAverageColor will be removed in elasticms/common-bundle 1.20. Use AssetRuntime::assetAverageColor instead.', E_USER_DEPRECATED);

        return $this->assetRuntime->assetAverageColor($hash);
    }
}
