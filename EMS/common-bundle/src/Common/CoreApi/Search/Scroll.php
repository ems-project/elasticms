<?php

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
    private Client $client;
    private Search $search;
    private string $expireTime;
    private ?string $nextScrollId;
    private int $currentPage;
    private ResponseInterface $currentResponse;
    private int $index = 0;

    public function __construct(Client $client, Search $search, string $expireTime = '3m')
    {
        $this->client = $client;
        $this->search = $search;
        $this->expireTime = $expireTime;
    }

    public function current(): DocumentInterface
    {
        return $this->currentResponse->getDocument($this->index);
    }

    public function next()
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
        $totalPages = \intval($this->search->getSize() > 0 ? \floor($count / $this->search->getSize()) : 0);
        $this->nextScrollId = $this->currentPage <= $totalPages ? $this->currentResponse->getScrollId() : null;
    }

    public function key(): string
    {
        if (null === $this->nextScrollId) {
            throw new \RuntimeException('Invalid scroll');
        }

        return $this->currentResponse->getDocument($this->index)->getId();
    }

    public function valid(): bool
    {
        return null !== $this->nextScrollId && $this->currentResponse->getTotalDocuments() > 0;
    }

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
