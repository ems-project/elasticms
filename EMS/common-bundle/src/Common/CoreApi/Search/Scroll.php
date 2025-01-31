<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi\Search;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Elasticsearch\Document\DocumentInterface;
use EMS\CommonBundle\Elasticsearch\Response\Response;
use EMS\CommonBundle\Elasticsearch\Response\ResponseInterface;
use EMS\CommonBundle\Search\Search;

/**
 * @implements \Iterator<string, DocumentInterface>
 */
class Scroll implements \Iterator
{
    private ?string $nextScrollId = null;
    private int $currentPage;
    private ResponseInterface $currentResponse;
    private int $index = 0;

    public function __construct(private readonly Client $client, private readonly Search $search, private readonly string $expireTime = '3m')
    {
    }

    #[\Override]
    public function current(): DocumentInterface
    {
        return $this->currentResponse->getDocument($this->index);
    }

    #[\Override]
    public function next(): void
    {
        ++$this->index;
        if ($this->index >= $this->currentResponse->getTotalDocuments()) {
            $this->nextScroll();
        }
    }

    private function nextScroll(): void
    {
        $this->currentResponse = Response::fromArray($this->client->post('/api/search/next-scroll', [
            'scroll-id' => $this->nextScrollId,
            'expire-time' => $this->expireTime,
        ])->getData());

        ++$this->currentPage;
        $this->setScrollId();
    }

    private function setScrollId(): void
    {
        $this->index = 0;
        $count = $this->currentResponse->getTotal();
        $totalPages = (int) ($this->search->getSize() > 0 ? \floor($count / $this->search->getSize()) : 0);
        $this->nextScrollId = $this->currentPage <= $totalPages ? $this->currentResponse->getScrollId() : null;
    }

    #[\Override]
    public function key(): string
    {
        if (null === $this->nextScrollId) {
            throw new \RuntimeException('Invalid scroll');
        }

        return $this->currentResponse->getDocument($this->index)->getId();
    }

    #[\Override]
    public function valid(): bool
    {
        return null !== $this->nextScrollId && $this->currentResponse->getTotalDocuments() > 0;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->initScroll();
    }

    private function initScroll(): void
    {
        $this->currentResponse = Response::fromArray($this->client->post('/api/search/init-scroll', [
            'search' => $this->search->serialize(),
            'expire-time' => $this->expireTime,
        ])->getData());
        $this->currentPage = 0;
        $this->setScrollId();
    }
}
