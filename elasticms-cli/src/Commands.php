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
    final public const string MEDIA_LIBRARY_UPDATE_FILE_LINKS = 'emscli:media-library:update-file-links';

    final public const string IMPORT_FILE = 'emscli:import:file';
    final public const string IMPORT_DATABASE = 'emscli:import:database';

    final public const string USERS_COLLECT_USERS = 'emscli:users:collect';

    final public const string MULTI_FILES_FIELD_TO_ARCHIVE = 'emscli:files-field:to-archive';
    final public const string DEAD_LINKS_REPORT = 'emscli:web:dead-links-report';
    final public const string FAKE_PROJECT_BUILD = 'emscli:dev:fake-project-build';
}
