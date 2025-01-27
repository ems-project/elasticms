<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common;

class EMSLinkCollection
{
    /** @var array<string, list<string>> */
    private array $links = [];

    public static function fromArray(EMSLink ...$links): self
    {
        $self = new self();

        foreach ($links as $link) {
            if (!$link->isValid() || isset($self->links[$link->getContentType()][$link->getOuuid()])) {
                continue;
            }

            $self->links[$link->getContentType()][] = $link->getOuuid();
        }

        if (0 === \count($self->links)) {
            throw new \RuntimeException('Empty link collection');
        }

        return $self;
    }

    /**
     * @param list<string> $emsIds
     */
    public static function fromEmsIds(array $emsIds): self
    {
        $emsLinks = \array_map(static fn (string $emsId) => EMSLink::fromText($emsId), $emsIds);

        return self::fromArray(...$emsLinks);
    }

    /** @return list<string> */
    public function getContentTypes(): array
    {
        return \array_keys($this->links);
    }

    /** @return list<string> */
    public function getOuuids(string $contentTypeName): array
    {
        return $this->links[$contentTypeName] ?? [];
    }
}
