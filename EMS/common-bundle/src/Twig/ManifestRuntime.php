<?php

namespace EMS\CommonBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;

class ManifestRuntime implements RuntimeExtensionInterface
{
    public function manifest(string $manifestUrl, string $resource): string
    {
        $contents = \file_get_contents($manifestUrl);

        if (false === $contents) {
            return $manifestUrl;
        }

        $manifest = \json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($manifest[$resource])) {
            return $manifestUrl;
        }

        $base = \preg_replace('/\/bundles\/.*\/manifest.json$/', '', $manifestUrl);

        return $base.'/'.$manifest[$resource];
    }
}
