<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Vite;

use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Json;

class ViteManifest
{
    private static ?ViteManifest $instance = null;

    /** @param array<string, array{file: string, name: string, css: ?string[]}> $manifest */
    private function __construct(private readonly array $manifest)
    {
    }

    public static function fromDirectory(string $directory): ?ViteManifest
    {
        $path = $directory.'/.vite/manifest.json';

        if (!\file_exists($path)) {
            return null;
        }

        if (null === self::$instance) {
            self::$instance = new self(Json::decode(File::fromFilename($path)->getContents()));
        }

        return self::$instance;
    }

    public function matchPath(string $path): ?string
    {
        if (\preg_match('/(?<path>.*\.(js|ts|cjs))(\.(?<index>[0-9]+))?\.css$/', $path, $matches) > 0
            && isset($this->manifest[$matches['path']]['css'][$matches['index'] ?? 0])) {
            return $this->manifest[$matches['path']]['css'][$matches['index'] ?? 0];
        }

        if (isset($this->manifest[$path]['file'])) {
            return $this->manifest[$path]['file'];
        }

        return $path;
    }
}
