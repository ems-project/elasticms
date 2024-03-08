<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision\Task;

use EMS\CoreBundle\Core\Mail\MailerService;
use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Entity\Revision;
use EMS\CoreBundle\Entity\Task;
use EMS\CoreBundle\Entity\UserInterface;
use EMS\CoreBundle\Routes;
use EMS\CoreBundle\Service\UserService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskMailer
{
    private const MAIL_TEMPLATE = '/revision/task/mail.twig';

    public function __construct(
        private readonly MailerService $mailerService,
        private readonly TaskManager $taskManager,
        private readonly UserService $userService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
        private readonly ?string $urlUser,
        private readonly string $templateNamespace
    ) {
    }

    public function sendForEvent(TaskEvent $event, string $type, string $receiverUsername): void
    {
        $task = $event->task;
        $revision = $event->revision;
        $senderUsername = $event->username;

        $sender = $this->userService->getUser($senderUsername);
        $receiver = $this->userService->getUser($receiverUsername);

        if (null === $receiver
            || !$receiver->getEmailNotification()
            || $receiver->getUsername() === $senderUsername) {
            return;
        }

        $mailTemplate = $this->mailerService->makeMailTemplate("@$this->templateNamespace".self::MAIL_TEMPLATE);
        $mailTemplate
            ->addTo($receiver->getEmail())
            ->setSubject(\sprintf('task.mail.%s.subject', $type), [
                '%title%' => $task->getTitle(),
                '%document%' => $event->revision->getLabel(),
            ])
            ->setBodyBlock('task_event_mail', [
                'receiver' => $receiver,
                'senderUsername' => $senderUsername,
                'senderRole' => $sender ? $this->getSenderRole($task, $sender) : null,
                'type' => $type,
                'action' => $this->translator->trans(\sprintf('task.mail.%s.action', $type), [], EMSCoreBundle::TRANS_DOMAIN),
                'task' => $task,
                'revision' => $revision,
                'comment' => $event->comment,
                'changeSet' => $event->changeSet,
                'backendUrl' => $this->urlUser,
                'documentUrl' => $this->getDocumentUrl($revision),
            ])
        ;

        $this->mailerService->sendMailTemplate($mailTemplate);
    }

    private function getDocumentUrl(Revision $revision): string
    {
        return $this->urlUser.$this->urlGenerator->generate(Routes::VIEW_REVISIONS, [
            'type' => $revision->giveContentType()->getName(),
            'ouuid' => $revision->getOuuid(),
        ]);
    }

    private function getSenderRole(Task $task, UserInterface $sender): ?string
    {
        return match (true) {
            ($sender->getUsername() === $task->getAssignee()) => 'assignee',
            ($sender->getUsername() === $task->getCreatedBy()) => 'creator',
            $this->taskManager->isTaskManager($sender) => 'task manager',
            default => null,
        };
    }
}
