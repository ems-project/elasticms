<?php

declare(strict_types=1);

namespace App\CLI;

class Commands
{
    final public const WEB_MIGRATION = 'emscli:web:migrate';
    final public const APPLE_PHOTOS_MIGRATION = 'emscli:apple-photos:migrate';
    final public const WEB_AUDIT = 'emscli:web:audit';
    final public const DOCUMENTS_UPDATE = 'emscli:documents:update';
    final public const MEDIA_LIBRARY_SYNC = 'emscli:media-library:synchronize';
    final public const FILE_READER_IMPORT = 'emscli:file-reader:import';
}
