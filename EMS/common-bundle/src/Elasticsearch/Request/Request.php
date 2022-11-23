<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Request;

class Request implements RequestInterface
{
    /** @var array<mixed> */
    private array $body;
    private string $index;
    private string $scroll = '30s';
    private int $size = 10;

    /**
     * @param array<mixed> $body
     */
    public function __construct(string $index, array $body)
    {
        $this->index = $index;
        $this->body = $body;
    }

    public function getScroll(): string
    {
        return $this->scroll;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'index' => $this->index,
            'scroll' => $this->scroll,
            'size' => $this->size,
        ];
    }
}
