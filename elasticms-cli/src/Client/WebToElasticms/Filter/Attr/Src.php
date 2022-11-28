<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Attr;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Helper\Url;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;

class Src
{
    public const TYPE = 'src';
    private ConfigManager $config;
    private string $currentUrl;
    private Rapport $rapport;

    public function __construct(ConfigManager $config, string $currentUrl, Rapport $rapport)
    {
        $this->config = $config;
        $this->currentUrl = $currentUrl;
        $this->rapport = $rapport;
    }

    /**
     * @return array{filename: string, filesize: int|null, mimetype: string, sha1: string}|array{}
     */
    public function process(string $href): array
    {
        $url = new Url($href, $this->currentUrl);

        return $this->config->urlToAssetArray($url, $this->rapport);
    }
}
