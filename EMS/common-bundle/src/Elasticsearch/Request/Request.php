<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Elasticsearch\Request;

class Request implements RequestInterface
{
    private string $scroll = '30s';
    private int $size = 10;

    /**
     * @param array<mixed> $body
     */
    public function __construct(private readonly string $index, private readonly array $body)
    {
    }

    #[\Override]
    public function getScroll(): string
    {
        return $this->scroll;
    }

    #[\Override]
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
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
