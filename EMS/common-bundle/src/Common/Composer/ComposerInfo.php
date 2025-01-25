<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Composer;

use EMS\Helpers\Standard\Json;

class ComposerInfo
{
    /** @var array<string, string> */
    private array $versionPackages = [];

    public const array PACKAGES = [
        'elasticms/core-bundle' => 'core',
        'elasticms/client-helper-bundle' => 'client',
        'elasticms/common-bundle' => 'common',
        'elasticms/form-bundle' => 'form',
        'elasticms/submission-bundle' => 'submission',
        'symfony/framework-bundle' => 'symfony',
    ];

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getVersionPackages(): array
    {
        return $this->versionPackages;
    }

    public function build(): void
    {
        $path = $this->projectDir.DIRECTORY_SEPARATOR.'composer.lock';
        $composerLockFile = Json::decodeFile($path);

        $allPackages = $composerLockFile['packages'] ?? [];
        $packages = \array_filter($allPackages, fn (array $p) => \array_key_exists($p['name'], self::PACKAGES));

        foreach ($packages as $p) {
            $shortname = self::PACKAGES[$p['name']];
            $this->versionPackages[$shortname] = $p['version'];
        }
    }
}
