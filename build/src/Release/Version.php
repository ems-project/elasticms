<?php

declare(strict_types=1);

namespace Build\Release;

use function Symfony\Component\String\u;

class Version
{
    public function __construct(
        public int $major,
        public int $minor,
        public int $patch
    ) {
    }

    public static function fromTag(string $version): Version
    {
        [$major, $minor, $patch] = \explode('.', $version);

        return new self((int) $major, (int) $minor, (int) $patch);
    }

    public function getTag(): string
    {
        return \implode('.', [$this->major, $this->minor, $this->patch]);
    }

    public function getBranchName(): string
    {
        return match ($this->getType()) {
            'minor', 'patch' => \sprintf('%s.%s', $this->major, $this->minor),
            default => \sprintf('%d.x', $this->major),
        };
    }

    public function getBranchDev(): string
    {
        return \sprintf('%s.x', $this->major);
    }

    public function getType(): string
    {
        return match (true) {
            u($this->getTag())->endsWith('.0.0') => 'major',
            u($this->getTag())->endsWith('.0') => 'minor',
            default => 'patch',
        };
    }
}
