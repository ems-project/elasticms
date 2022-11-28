<?php

declare(strict_types=1);

namespace App\CLI\Helper;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;

class StringStream extends Stream implements StreamInterface
{
    public function __construct(string $text)
    {
        $resource = \fopen('php://memory', 'rw+');
        if (false === $resource) {
            throw new \RuntimeException('Unexpected false in memory file');
        }
        parent::__construct($resource);
        $this->write($text);
        $this->rewind();
    }
}
