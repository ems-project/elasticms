<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision\Task\DataTable;

class TasksDataTableFilters
{
    /** @var string[] */
    public array $status = [];
    /** @var string[] */
    public array $assignee = [];
    /** @var string[] */
    public array $requester = [];
}
