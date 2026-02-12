<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\Routing\Url\Transformer;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

class RoutingExtension
{
    public function __construct(private readonly Transformer $transformer)
    {
    }

    /**
     * @param array<mixed> $parameters
     */
    #[AsTwigFunction(name: 'emsch_route')]
    public function createUrl(string $relativePath, string $path, array $parameters = []): string
    {
        $url = $this->transformer->getGenerator()->createUrl($relativePath, $path);

        if ($parameters) {
            $url .= '?'.\http_build_query($parameters);
        }

        return $url;
    }

    #[AsTwigFilter(name: 'emsch_routing', isSafe: ['html'])]
    public function transform(string $content, ?string $locale = null, ?string $baseUrl = null): string
    {
        return $this->transformer->transform($content, ['locale' => $locale, 'baseUrl' => $baseUrl]);
    }

    /**
     * @param array<mixed> $config
     */
    #[AsTwigFilter(name: 'emsch_routing_config', isSafe: ['html'])]
    public function transformConfig(string $content, array $config): string
    {
        return $this->transformer->transform($content, $config);
    }
}
