<?php

declare(strict_types=1);

namespace App\CLI\Helper;

class LighthouseWrapper extends ProcessWrapper
{
    public function __construct(string $url, float $timeout = 5 * 60.0)
    {
        parent::__construct([
            './node_modules/lighthouse/lighthouse-cli/index.js',
            $url,
            '--output=json',
            '--preset=desktop',
            '--quiet',
            '--only-categories=accessibility,best-practices,performance,seo',
            '--chrome-flags=\'--headless --disable-gpu --no-sandbox\'',
        ], null, $timeout);
    }
}
