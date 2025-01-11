<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\Routing\Url\Transformer;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class RoutingRuntime implements RuntimeExtensionInterface
{
    public function __construct(private Transformer $transformer)
    {
    }

    /**
     * @param array<mixed> $parameters
     */
    public function createUrl(string $relativePath, string $path, array $parameters = []): string
    {
        $url = $this->transformer->getGenerator()->createUrl($relativePath, $path);

        if ($parameters) {
            $url .= '?'.\http_build_query($parameters);
        }

        return $url;
    }

    public function transform(string $content, ?string $locale = null, ?string $baseUrl = null): string
    {
        return $this->transformer->transform($content, ['locale' => $locale, 'baseUrl' => $baseUrl]);
    }

    /**
     * @param array<mixed> $config
     */
    public function transformConfig(string $content, array $config): string
    {
        return $this->transformer->transform($content, $config);
    }
}
