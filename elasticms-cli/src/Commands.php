<?php

declare(strict_types=1);

namespace App\CLI;

class Commands
{
    final public const string WEB_MIGRATION = 'emscli:web:migrate';
    final public const string APPLE_PHOTOS_MIGRATION = 'emscli:apple-photos:migrate';
    final public const string FILE_AUDIT = 'emscli:file:audit';
    final public const string WEB_AUDIT = 'emscli:web:audit';
    final public const string DOCUMENTS_UPDATE = 'emscli:documents:update';
    final public const string MEDIA_LIBRARY_SYNC = 'emscli:media-library:synchronize';
    final public const string MEDIA_LIBRARY_TIKA_CACHE = 'emscli:media-library:load-tika-cache';
    final public const string FILE_READER_IMPORT = 'emscli:file-reader:import';
}
