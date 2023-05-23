<?php

declare(strict_types=1);

namespace App\CLI\Client\WebToElasticms\Filter\Attr;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Helper\Url;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;

class LinkMediaFile
{
    final public const TYPE = 'link-media-file';

    public function __construct(private readonly ConfigManager $config, private readonly Rapport $rapport)
    {
    }

    public function process(Url $href, string $attribute): string
    {
        $path = $this->config->mediaFile($href, $this->rapport, $attribute);

        if (null == $path) {
            return '';
        }

        return $path;
    }
}
