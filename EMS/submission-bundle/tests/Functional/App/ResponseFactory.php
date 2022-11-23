<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\App;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResponseFactory
{
    /** @var callable|null */
    private $callback;

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        if (\is_callable($this->callback)) {
            return ($this->callback)($method, $url, $options);
        }

        return new MockResponse();
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }
}
