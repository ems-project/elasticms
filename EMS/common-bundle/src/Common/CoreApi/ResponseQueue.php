<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ResponseQueue implements \Countable
{
    private int $count = 0;
    /** @var ResponseInterface[] */
    private array $responses = [];

    public function __construct(private readonly int $flushSize)
    {
    }

    #[\Override]
    public function count(): int
    {
        return \max($this->count, 0);
    }

    public function add(ResponseInterface $response): self
    {
        $this->responses[] = $response;
        ++$this->count;

        if ($this->flushSize > 0 && \count($this->responses) === $this->flushSize) {
            $this->flush();
        }

        return $this;
    }

    public function flush(): self
    {
        foreach (\array_keys($this->responses) as $i) {
            unset($this->responses[$i]);
        }

        return $this;
    }
}
