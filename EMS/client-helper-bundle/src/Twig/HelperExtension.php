<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Twig;

use EMS\ClientHelperBundle\Helper\Asset\AssetHelperRuntime;
use EMS\ClientHelperBundle\Helper\Elasticsearch\ClientRequestRuntime;
use EMS\CommonBundle\Twig\AssetRuntime;
use Twig\DeprecatedCallableInfo;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class HelperExtension extends AbstractExtension
{
    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('emsch_routing', [RoutingRuntime::class, 'transform'], ['is_safe' => ['html']]),
            new TwigFilter('emsch_routing_config', [RoutingRuntime::class, 'transformConfig'], ['is_safe' => ['html']]),
            new TwigFilter('emsch_get', [ClientRequestRuntime::class, 'get']),
            // deprecated
            new TwigFilter('emsch_data', [ClientRequestRuntime::class, 'data'], ['deprecated' => true, 'alternative' => 'emsch_get']),
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('emsch_admin_menu', [AdminMenuRuntime::class, 'showAdminMenu'], ['is_safe' => ['html']]),
            new TwigFunction('emsch_route', [RoutingRuntime::class, 'createUrl']),
            new TwigFunction('emsch_search', [ClientRequestRuntime::class, 'search']),
            new TwigFunction('emsch_search_one', [ClientRequestRuntime::class, 'searchOne']),
            new TwigFunction('emsch_add_environment', [ClientRequestRuntime::class, 'addEnvironment']),
            new TwigFunction('emsch_search_config', [ClientRequestRuntime::class, 'searchConfig']),
            new TwigFunction('emsch_http_error', [ClientRequestRuntime::class, 'httpException']),
            new TwigFunction('emsch_asset', [AssetHelperRuntime::class, 'asset'], ['is_safe' => ['html']]),
            new TwigFunction('emsch_assets', [AssetHelperRuntime::class, 'assets'], [
                'deprecation_info' => new DeprecatedCallableInfo('elasticms/client-helper-bundle', '5.19.0', 'emsch_assets_version'),
            ]),
            new TwigFunction('emsch_assets_version', [AssetHelperRuntime::class, 'setVersion']),
            new TwigFunction('emsch_unzip', [AssetRuntime::class, 'unzip'], [
                'deprecation_info' => new DeprecatedCallableInfo('elasticms/client-helper-bundle', '5.19.0', 'ems_unzip', 'elasticms/common-bundle', '5.19.0'),
            ]),
        ];
    }
}
