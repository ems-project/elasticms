<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision\Task;

use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Entity\Task;

/**
 * @implements \IteratorAggregate<int, Task>
 */
final class TaskCollection implements \IteratorAggregate
{
    /** @var Task[] */
    private array $tasks = [];

    public function __construct(private readonly Revision $revision)
    {
    }

    /**
     * @return \ArrayIterator<int, Task>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tasks);
    }

    /**
     * @param Task[] $tasks
     */
    public function addTasks(array $tasks): void
    {
        foreach ($tasks as $task) {
            $this->tasks[] = $task;
        }
    }

    public function getRevision(): Revision
    {
        return $this->revision;
    }
}
