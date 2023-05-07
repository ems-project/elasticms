<?php

namespace App\CLI\Helper\Tika;

interface TikaPromiseInterface
{
    public function getText(): string;
}
