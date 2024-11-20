<?php

declare(strict_types=1);

namespace Build\Doc\Markdown;

use function Symfony\Component\String\u;

class Block
{
    /** @var array<string, Block> */
    private array $sections = [];
    public Content $content;

    public function __construct(
        public readonly string $title,
        public readonly int $level,
        public readonly bool $sortSections = false
    ) {
        $this->content = new Content();
    }

    public function __toString(): string
    {
        $dump = '';
        if ($this->level > 0) {
            $prefix = u('#')->repeat($this->level)->append(' ')->toString();
            $dump .= u($this->title)->prepend($prefix)->toString().\PHP_EOL;
        }

        $dump .= $this->content;

        return \array_reduce($this->sections, static fn (string $carry, Block $block) => $carry.$block, $dump);
    }

    /**
     * @param string[] $lines
     */
    public static function fromLines(string $title, array $lines, int $level = 0): Block
    {
        $block = new self($title, $level);

        $flagInCodeBlock = false;
        foreach ($lines as $i => $line) {
            if (\str_starts_with(\ltrim($line), '```')) {
                $flagInCodeBlock = !$flagInCodeBlock;
            }
            if (\str_starts_with($line, '#') && !$flagInCodeBlock) {
                break;
            }
            if (\str_starts_with($line, Content::AUTO_GENERATED)) {
                $block->content->startStopAutoGeneration($line);
            } else {
                $block->content->write($line);
            }
            unset($lines[$i]);
        }

        $childrenPrefix = u('#')->repeat($level + 1)->append(' ')->toString();
        $currentChild = false;
        $groupChildren = [];

        $flagInCodeBlock = false;
        foreach ($lines as $line) {
            if (\str_starts_with(\ltrim($line), '```')) {
                $flagInCodeBlock = !$flagInCodeBlock;
            }
            if (\str_starts_with($line, $childrenPrefix) && !$flagInCodeBlock) {
                $title = u($line)->trimPrefix($childrenPrefix)->toString();
                $groupChildren[$title] = [];
                $currentChild = $title;
            } elseif ($currentChild) {
                $groupChildren[$currentChild][] = $line;
            }
        }

        foreach ($groupChildren as $title => $lines) {
            $block->addSection(Block::fromLines($title, $lines, $level + 1));
        }

        return $block;
    }

    public function getSection(string $title, bool $sort = true): Block
    {
        if (!isset($this->sections[$title])) {
            $this->addSection(new self(
                title: $title,
                level: $this->level + 1,
                sortSections: $sort
            ));
        }

        return $this->sections[$title];
    }

    private function addSection(Block $section): void
    {
        $this->sections[$section->title] = $section;
        \ksort($this->sections);
    }
}
