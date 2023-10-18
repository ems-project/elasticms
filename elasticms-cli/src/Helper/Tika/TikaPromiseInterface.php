<?php

declare(strict_types=1);

namespace App\CLI\Helper\Tika;

interface TikaPromiseInterface
{
    public function startText(): void;

    public function getText(): string;

    public function startMeta(): void;

    public function getMeta(): TikaMeta;

    public function startHtml(): void;

    public function getHtml(): string;
}
