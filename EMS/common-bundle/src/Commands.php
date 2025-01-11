<?php

declare(strict_types=1);

namespace EMS\CommonBundle;

class Commands
{
    final public const string BATCH = 'ems:batch';
    final public const string CURL = 'ems:curl';
    final public const string STATUS = 'ems:status';
    final public const string CLEAR_LOGS = 'ems:logs:clear';
    final public const string CLEAR_CACHE = 'ems:storage:clear-cache';
    final public const string LOAD_ARCHIVE_IN_CACHE = 'ems:storage:load-archive-in-cache';
    final public const string ADMIN_COMMAND = 'ems:admin:command';
    final public const string ADMIN_NEXT_JOB = 'ems:admin:next-job';
    final public const string FILE_STRUCTURE_PUBLISH = 'ems:file-structure:publish';
    final public const string FILE_STRUCTURE_PULL = 'ems:file-structure:pull';
    final public const string FILE_STRUCTURE_PUSH = 'ems:file-structure:push';
    final public const string SUBMISSION_FORWARD = 'ems:submissions:forward';
}
