<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Twig;

use EMS\Helpers\Standard\Json;
use Twig\Extension\RuntimeExtensionInterface;

class ManifestRuntime implements RuntimeExtensionInterface
{
    public function manifest(string $manifestUrl, string $resource): string
    {
        $contents = \file_get_contents($manifestUrl);

        if (false === $contents) {
            return $manifestUrl;
        }

        $manifest = Json::decode($contents);

        if (!isset($manifest[$resource])) {
            return $manifestUrl;
        }

        $base = \preg_replace('/\/bundles\/.*\/manifest.json$/', '', $manifestUrl);

        return $base.'/'.$manifest[$resource];
    }
}
