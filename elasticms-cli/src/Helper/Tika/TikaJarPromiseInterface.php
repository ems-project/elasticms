<?php

namespace App\CLI\Helper\Tika;

use App\CLI\Helper\ProcessWrapper;
use Psr\Http\Message\StreamInterface;

class TikaJarPromiseInterface implements TikaPromiseInterface
{
    private ProcessWrapper $textWrapper;

    public function __construct(StreamInterface $stream)
    {
        $this->textWrapper = TikaWrapper::getText($stream, \sys_get_temp_dir());
        $this->textWrapper->start();
    }

    public function getText(): string
    {
        return $this->textWrapper->getOutput();
    }
}
