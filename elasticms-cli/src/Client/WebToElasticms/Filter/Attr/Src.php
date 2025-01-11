<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Attr;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use EMS\CommonBundle\Helper\Url;

class Src
{
    final public const string TYPE = 'src';

    public function __construct(private readonly ConfigManager $config, private readonly string $currentUrl, private readonly Rapport $rapport)
    {
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
