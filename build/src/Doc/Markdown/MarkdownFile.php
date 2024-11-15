<?php

declare(strict_types=1);

namespace Build\Doc\Markdown;

class MarkdownFile
{
    public Block $block;

    public function __construct(private readonly string $filename)
    {
        if (!\file_exists($this->filename)) {
            \touch($this->filename);
        }

        if (false === $contents = \file_get_contents($this->filename)) {
            throw new \RuntimeException(\sprintf('Could not read content of %s', $this->filename));
        }

        $lines = \preg_split('/\r\n|\r|\n/', $contents);

        $this->block = \is_array($lines) ? Block::fromLines('document', $lines) : new Block('document', 0);
    }

    public function __destruct()
    {
        \file_put_contents($this->filename, (string) $this->block);
    }
}
