<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Attr;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;

class DataLink
{
    final public const string TYPE = 'data-link';

    public function __construct(private readonly ConfigManager $config, private readonly string $currentUrl, private readonly Rapport $rapport)
    {
    }

    public function process(string $href, string $type): string
    {
        $path = $this->config->findDataLink($href, $this->rapport, $this->currentUrl, $type);

        return $path;
    }
}
