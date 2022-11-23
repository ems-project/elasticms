<?php

declare(strict_types=1);

namespace App\Helper;

class Pa11yWrapper extends ProcessWrapper
{
    public function __construct(string $url, string $standard = 'WCAG2AA', float $timeout = 3 * 60.0)
    {
        parent::__construct([
            './node_modules/pa11y/bin/pa11y.js',
            '-s',
            $standard,
            '-r',
            'json',
            $url,
        ], null, $timeout);
    }
}
