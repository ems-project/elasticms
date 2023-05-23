<?php

namespace App\CLI\Helper\Tika;

class TikaMeta
{
    /**
     * @param string[] $meta
     */
    public function __construct(private readonly array $meta)
    {
    }

    /**
     * @return string[]
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getLocale(): ?string
    {
        return $this->meta['language'] ?? null;
    }

    public function getTitle(): ?string
    {
        return $this->meta['dc:title'] ?? null;
    }

    public function getCreator(): ?string
    {
        return $this->meta['dc:creator'] ?? null;
    }

    public function getKeyword(): ?string
    {
        return $this->meta['meta:keyword'] ?? null;
    }

    public function getPublisher(): ?string
    {
        return $this->meta['dc:publisher'] ?? null;
    }

    public function getModified(): ?\DateTimeImmutable
    {
        return $this->toDateTimeImmutable('dcterms:modified');
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->toDateTimeImmutable('dcterms:created');
    }

    private function toDateTimeImmutable(string $attr): ?\DateTimeImmutable
    {
        $value = $this->meta[$attr] ?? null;
        if (null === $value) {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, $value);
        if (false === $date) {
            throw new \RuntimeException(\sprintf('Unexpected false ATOM date from %s', $value));
        }

        return $date;
    }
}
