<?php

declare(strict_types=1);

namespace Build\Doc\Markdown;

use function Symfony\Component\String\u;

class Content
{
    /** @var array<int|string, string|string[]> */
    private array $lines = [];
    private ?string $autoGeneration = null;

    public const AUTO_GENERATED = '[//]: auto-generated';

    public function __toString(): string
    {
        $content = '';

        $lines = $this->removeDubbleLineBreaks($this->lines);
        foreach ($lines as $name => $line) {
            $content .= match (true) {
                \is_string($line) => $line.\PHP_EOL,
                \is_string($name) && \is_array($line) => $this->createGeneratedBlock($name, $line),
                default => throw new \RuntimeException('invalid line type'),
            };
        }

        return $content;
    }

    public function startStopAutoGeneration(string $generation): void
    {
        if (\str_starts_with($generation, self::AUTO_GENERATED)) {
            $generation = u($generation)->trimPrefix(self::AUTO_GENERATED.'-')->toString();
        }

        $this->autoGeneration = ($this->autoGeneration === $generation) ? null : $generation;

        if ($this->autoGeneration) {
            $this->lines[$this->autoGeneration] = [];
        }
    }

    public function newLine(): void
    {
        $this->write('');
    }

    public function write(string $line): void
    {
        if ($this->autoGeneration && \is_array($this->lines[$this->autoGeneration])) {
            $this->lines[$this->autoGeneration][] = $line;
        } else {
            $this->lines[] = $line;
        }
    }

    /**
     * @param string[] $lines
     */
    private function createGeneratedBlock(string $name, array $lines): string
    {
        $comment = \sprintf('%s-%s', self::AUTO_GENERATED, $name);
        /** @var string[] $lines */
        $lines = $this->removeDubbleLineBreaks($lines);

        return \implode(\PHP_EOL, [$comment, ...$lines, \PHP_EOL.$comment.\PHP_EOL]);
    }

    /**
     * @param array<int|string, string|string[]> $lines
     *
     * @return array<int|string, string|string[]>
     */
    private function removeDubbleLineBreaks(array $lines): array
    {
        $result = [];

        foreach ($lines as $key => $line) {
            if ('' !== $line || '' !== \end($result)) {
                $result[$key] = $line;
            }
        }

        return $result;
    }
}
