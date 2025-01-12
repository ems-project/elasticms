<?php

declare(strict_types=1);

namespace App\CLI\Helper;

use EMS\Helpers\Standard\Text;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AsyncResponse
{
    public function __construct(private readonly ResponseInterface $response, private readonly bool $trimWhiteSpaces = true)
    {
    }

    public function getContent(): string
    {
        return $this->trimWhiteSpaces ? Text::superTrim($this->response->getContent()) : $this->response->getContent();
    }

    /**
     * @return string[]
     */
    public function getJson(): array
    {
        return $this->response->toArray();
    }
}
