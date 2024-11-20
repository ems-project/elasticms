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

    public function startStopAutoGeneration(string $generation): self
    {
        if (\str_starts_with($generation, self::AUTO_GENERATED)) {
            $generation = u($generation)->trimPrefix(self::AUTO_GENERATED.'-')->toString();
        }

        $this->autoGeneration = ($this->autoGeneration === $generation) ? null : $generation;

        if ($this->autoGeneration) {
            $this->lines[$this->autoGeneration] = [];
        }

        return $this;
    }

    public function newLine(): self
    {
        $this->write('');

        return $this;
    }

    public function write(?string $line, bool $newLine = false): self
    {
        if (null === $line) {
            return $this;
        }

        if ($this->autoGeneration && \is_array($this->lines[$this->autoGeneration])) {
            $this->lines[$this->autoGeneration][] = $line;
            if ($newLine) {
                $this->lines[$this->autoGeneration][] = '';
            }
        } else {
            $this->lines[] = $line;
            if ($newLine) {
                $this->newLine();
            }
        }

        return $this;
    }

    public function writeCode(string $type, string ...$lines): self
    {
        $this->write(\sprintf('```%s', $type));
        foreach ($lines as $line) {
            $this->write($line);
        }
        $this->write('```')->newLine();

        return $this;
    }

    /**
     * @param string[] $lines
     */
    private function createGeneratedBlock(string $name, array $lines): string
    {
        $comment = \sprintf('%s-%s', self::AUTO_GENERATED, $name);

        /** @var string[] $generatedBlock */
        $generatedBlock = [$comment, '', ...$lines, '', $comment, ''];

        /** @var string[] $lines */
        $lines = $this->removeDubbleLineBreaks($generatedBlock);

        return \implode(\PHP_EOL, $lines);
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
            if ('' !== $line || ('' !== \end($result))) {
                $result[$key] = $line;
            }
        }

        return $result;
    }

    /**
     * @param string[] $headers
     * @param string[] $rows
     */
    public function table(array $headers, array $rows): self
    {
        if (0 === \count($rows) || 0 === \count($headers)) {
            return $this;
        }

        /** @var array<int, array<int, string>> $data */
        $data = [\array_values($headers), ...\array_values($rows)];

        $columnsLength = \array_map(
            fn ($col) => \max(\array_map(fn (?string $v) => $v ? \strlen($v) : 0, $col)),
            (\count($data) > 1) ? \array_map(null, ...$data) : \array_map(null, $data)
        );

        foreach ($data as $i => $row) {
            foreach ($columnsLength as $index => $length) {
                $row[$index] = u($row[$index] ?? '')->padEnd($length)->toString();
            }

            $this->write('| '.\implode(' | ', $row).' |');

            if (0 === $i) {
                $seperator = \array_map(fn (int $length) => u('-')->repeat($length), $columnsLength);
                $this->write('| '.\implode(' | ', $seperator).' |');
            }
        }

        return $this;
    }

    /**
     * @param array<int, string|array{ 'title': string, 'content': string[] }> $items
     */
    public function list(array $items): self
    {
        if (0 === \count($items)) {
            return $this;
        }

        foreach ($items as $item) {
            if (\is_string($item)) {
                $this->write('* '.$item);
                continue;
            }

            $this->write('* '.$item['title']);
            $content = $item['content'] ?? [];

            foreach ($content as $line) {
                $this->write('  '.$line);
            }
        }

        $this->newLine();

        return $this;
    }
}
