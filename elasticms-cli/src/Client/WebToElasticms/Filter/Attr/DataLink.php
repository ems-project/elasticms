<?php

declare(strict_types=1);

namespace App\Client\WebToElasticms\Filter\Attr;

use App\Client\WebToElasticms\Config\ConfigManager;
use App\Client\WebToElasticms\Rapport\Rapport;

class DataLink
{
    public const TYPE = 'data-link';
    private ConfigManager $config;
    private string $currentUrl;
    private Rapport $rapport;

    public function __construct(ConfigManager $config, string $currentUrl, Rapport $rapport)
    {
        $this->config = $config;
        $this->currentUrl = $currentUrl;
        $this->rapport = $rapport;
    }

    public function process(string $href, string $type): string
    {
        $path = $this->config->findDataLink($href, $this->rapport, $this->currentUrl, $type);

        return $path;
    }
}
