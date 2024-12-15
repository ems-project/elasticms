<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\CoreApi;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ResponseQueue implements \Countable
{
    private int $count = 0;
    /** @var ResponseInterface[] */
    private array $responses = [];
    /** @var callable */
    private $flushCallback;

    public function __construct(private readonly int $flushSize)
    {
    }

    public function count(): int
    {
        return $this->count;
    }

    public function addFlushCallback(callable $callback): self
    {
        $this->flushCallback = $callback;

        return $this;
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
        foreach ($this->responses as $i => $response) {
            if ($this->flushCallback) {
                ($this->flushCallback)($response);
            }
            unset($this->responses[$i]);
        }

        return $this;
    }
}
