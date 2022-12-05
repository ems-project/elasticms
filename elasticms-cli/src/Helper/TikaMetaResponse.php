<?php

namespace App\CLI\Helper;

class TikaMetaResponse
{
    /** @var string[]|null */
    private ?array $meta = null;

    public function __construct(private readonly AsyncResponse $response)
    {
    }

    /**
     * @return string[]
     */
    public function getMeta(): array
    {
        if (null !== $this->meta) {
            return $this->meta;
        }
        $this->meta = $this->response->getJson();

        return $this->meta;
    }

    public function getLocale(): ?string
    {
        return $this->getMeta()['language'] ?? null;
    }

    public function getTitle(): ?string
    {
        return $this->getMeta()['dc:title'] ?? null;
    }

    public function getCreator(): ?string
    {
        return $this->getMeta()['dc:creator'] ?? null;
    }

    public function getKeyword(): ?string
    {
        return $this->getMeta()['meta:keyword'] ?? null;
    }

    public function getPublisher(): ?string
    {
        return $this->getMeta()['dc:publisher'] ?? null;
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
        $value = $this->getMeta()[$attr] ?? null;
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
