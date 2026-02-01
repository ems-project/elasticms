<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Tika;

use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Type;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class TikaJar implements CacheWarmerInterface
{
    private ?TikaWrapper $wrapper = null;

    public function __construct(
        private readonly ?string $tikaServer,
        private readonly ?string $tikaDownloadUrl,
        private readonly string $projectDir,
    ) {
    }

    public function getWrapper(): TikaWrapper
    {
        if ($this->wrapper instanceof TikaWrapper) {
            return $this->wrapper;
        }

        $filename = $this->projectDir.'/var/tika-app.jar';
        if (!\file_exists($filename) && $this->tikaDownloadUrl) {
            try {
                File::putContents($filename, Type::string(\fopen($this->tikaDownloadUrl, 'r')));
            } catch (\Throwable) {
                if (\file_exists($filename)) {
                    \unlink($filename);
                }
            }
        }

        if (!\file_exists($filename)) {
            throw new \RuntimeException("Tika's jar not found");
        }

        $this->wrapper = new TikaWrapper($filename);

        return $this->wrapper;
    }

    #[\Override]
    public function isOptional(): bool
    {
        return false;
    }

    #[\Override]
    public function warmUp($cacheDir, ?string $buildDir = null): array
    {
        if (empty($this->tikaServer)) {
            $this->getWrapper();
        }

        return [];
    }
}
