<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision\Task;

use EMS\CoreBundle\Core\Mail\MailerService;
use EMS\CoreBundle\Service\UserService;

class TaskMailer
{
    private const MAIL_TEMPLATE = '/revision/task/mail.twig';

    public function __construct(
        private readonly MailerService $mailerService,
        private readonly UserService $userService,
        private readonly ?string $urlUser,
        private readonly string $templateNamespace
    ) {
    }

    public function sendForEvent(TaskEvent $event, string $type, string $receiverUsername): void
    {
        $task = $event->task;
        $revision = $event->revision;
        $receiver = $this->userService->getUser($receiverUsername);

        if (null === $receiver
            || !$receiver->getEmailNotification()
            || $receiver->getUsername() === $event->username) {
            return;
        }

        $mailTemplate = $this->mailerService->makeMailTemplate("@$this->templateNamespace".self::MAIL_TEMPLATE);
        $mailTemplate
            ->addTo($receiver->getEmail())
            ->setSubject(\sprintf('task.mail.%s', $type), [
                '%title%' => $task->getTitle(),
                '%document%' => $event->revision->getLabel(),
            ])
            ->setBodyBlock(\sprintf('mail_%s', $type), [
                'receiver' => $receiver,
                'task' => $task,
                'revision' => $revision,
                'comment' => $event->comment,
                'changeSet' => $event->changeSet,
                'backendUrl' => $this->urlUser,
            ])
        ;

        $this->mailerService->sendMailTemplate($mailTemplate);
    }
}
