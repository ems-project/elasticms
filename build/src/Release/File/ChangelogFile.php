<?php

declare(strict_types=1);

namespace Build\Release\File;

use Build\Release\Version;
use EMS\Helpers\File\File;

class ChangelogFile
{
    private string $filename;
    /** @var array<string, string[]> */
    private array $releases = [];

    public const TYPES = [
        'feat' => 'Features',
        'fix' => 'Bug Fixes',
        'docs' => 'Documentation',
        'style' => 'Styles',
        'refactor' => 'Code Refactoring',
        'perf' => 'Performance Improvements',
        'test' => 'Tests',
        'build' => 'Builds',
        'ci' => 'Continuous Integrations',
        'chore' => 'Chores',
        'revert' => 'Reverts',
    ];

    private function __construct(private readonly Version $version)
    {
        $this->filename = \sprintf(__DIR__.'/../../../../CHANGELOG-%d.x.md', $this->version->major);
        if (!\file_exists($this->filename)) {
            \touch($this->filename);
            \file_put_contents($this->filename, $this->getTitle().\PHP_EOL);
        }

        $this->parse(File::fromFilename($this->filename)->getContents());
    }

    public static function create(Version $version): self
    {
        return new self($version);
    }

    public function add(Changes $changes): ChangelogFile
    {
        $latest = \array_key_first($this->releases);

        if (\str_starts_with((string) $latest, '## '.$changes->version->getTag())) {
            unset($this->releases[$latest]);
        }

        $date = (new \DateTime('now'))->format('Y-m-d');
        $release = \sprintf('## %s (%s)', $this->version->getTag(), $date);

        $this->releases = [...[$release => []], ...$this->releases];

        foreach ($changes as $type => $pullRequests) {
            $this->releases[$release][] = \sprintf('### %s', self::TYPES[$type]);
            foreach ($pullRequests as $pullRequest) {
                $this->releases[$release][] = $pullRequest;
            }
        }

        return $this;
    }

    public function write(): void
    {
        $content = $this->getTitle().\PHP_EOL.\PHP_EOL;

        foreach ($this->releases as $release => $lines) {
            $content .= $release.\PHP_EOL.\implode(\PHP_EOL, $lines).\PHP_EOL.\PHP_EOL;
        }

        \file_put_contents($this->filename, $content);
    }

    private function parse(string $content): void
    {
        $separator = "\r\n";
        $line = \strtok($content, $separator);

        $lines = [];
        while (false !== $line) {
            $lines[] = $line;
            $line = \strtok($separator);
        }

        $currentRelease = null;
        foreach ($lines as $line) {
            if (\str_starts_with($line, '## ')) {
                $currentRelease = $line;
                $this->releases[$currentRelease] = [];
            }
            if (\str_starts_with($line, '### ') || \str_starts_with($line, '* ')) {
                $this->releases[$currentRelease][] = $line;
            }
        }
    }

    private function getTitle(): string
    {
        return \sprintf('# Changelog %d.x', $this->version->major);
    }
}
