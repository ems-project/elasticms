<?php

declare(strict_types=1);

namespace App\CLI;

class Commands
{
    public const WEB_MIGRATION = 'emscli:web:migrate';
    public const APPLE_PHOTOS_MIGRATION = 'emscli:apple-photos:migrate';
    public const WEB_AUDIT = 'emscli:web:audit';
    public const DOCUMENTS_UPDATE = 'emscli:documents:update';
}
