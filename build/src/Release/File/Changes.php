<?php

declare(strict_types=1);

namespace Build\Release\File;

use Build\Release\Version;

/**
 * @implements \IteratorAggregate<string, string[]>
 */
class Changes implements \IteratorAggregate
{
    /** @var array<string, string[]> */
    private array $changes;

    public function __construct(
        public readonly Version $version,
        public readonly string $body,
    ) {
        $this->changes = $this->build($body);
    }

    /**
     * @return \ArrayIterator<string, string[]>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->changes);
    }

    /**
     * @return string[]
     */
    public function list(): array
    {
        $list = [];
        foreach ($this->changes as $type => $pullRequests) {
            $list[] = \sprintf('<comment># %s</comment>', ChangelogFile::TYPES[$type]);
            $list = [...$list, ...$pullRequests];
        }

        return $list;
    }

    /**
     * @return array<string, string[]>
     */
    private function build(string $releaseNotes): array
    {
        $changelog = \array_map(static fn (string $title) => [], ChangelogFile::TYPES);
        $regex = \sprintf('/^\*.(?<type>%s).*/s', \implode('|', \array_keys(ChangelogFile::TYPES)));

        $line = \strtok($releaseNotes, \PHP_EOL);
        while (false !== $line) {
            if (\preg_match($regex, $line, $matches)) {
                $changelog[$matches['type']][] = $line;
            }
            $line = \strtok(\PHP_EOL);
        }

        $changelog = \array_filter($changelog);
        \array_walk($changelog, static fn (array &$pullRequests) => \sort($pullRequests));

        return $changelog;
    }
}
