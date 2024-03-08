<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Command\Revision\Task;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CoreBundle\Commands;
use EMS\CoreBundle\Core\Revision\Task\TaskMailer;
use EMS\CoreBundle\Core\Revision\Task\TaskManager;
use EMS\CoreBundle\Core\Revision\Task\TaskStatus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class TaskNotificationMailCommand extends AbstractCommand
{
    private string $subject;

    protected static $defaultName = Commands::REVISION_TASK_NOTIFICATION_MAIL;
    private const OPTION_SUBJECT = 'subject';

    public function __construct(
        private readonly TaskManager $taskManager,
        private readonly TaskMailer $taskMailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send notification mail for tasks')
            ->addOption(self::OPTION_SUBJECT, null, InputOption::VALUE_REQUIRED, 'Set mail subject', 'notification tasks');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->io->title('EMS - Revision - Task notification mail');

        $this->subject = $this->getOptionString(self::OPTION_SUBJECT);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $revisionsByReceiver = [];
        $revisionsWithCurrentTask = $this->taskManager->getRevisionsWithCurrentTask();

        foreach ($revisionsWithCurrentTask as $revision) {
            $task = $revision->getTaskCurrent();
            $taskStatus = TaskStatus::from($task->getStatus());

            if (TaskStatus::PROGRESS === $taskStatus || TaskStatus::REJECTED === $taskStatus) {
                $revisionsByReceiver[$task->getAssignee()][] = $revision;
            }
            if (TaskStatus::COMPLETED === $taskStatus) {
                $revisionsByReceiver[$task->getCreatedBy()][] = $revision;
            }
        }

        foreach ($revisionsByReceiver as $receiver => $revisions) {
            $this->taskMailer->sendNotificationMail($receiver, $this->subject, $revisions);
        }

        return self::EXECUTE_SUCCESS;
    }
}
