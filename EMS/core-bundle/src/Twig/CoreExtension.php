<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Twig;

use EMS\CoreBundle\Core\Mail\MailerService;
use EMS\CoreBundle\Core\Revision\Json\JsonMenuRenderer;
use EMS\CoreBundle\Event\DispatchToWebhookEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;

readonly class CoreExtension
{
    public function __construct(
        private MailerService $mailer,
        private JsonMenuRenderer $jsonMenuRenderer,
        private LoggerInterface $logger,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    #[AsTwigFunction(name: 'emsco_json_menu_nested', isSafe: ['html'])]
    public function generateNested(array $options, string $type = JsonMenuRenderer::TYPE_VIEW): string
    {
        return $this->jsonMenuRenderer->generateNested($options, $type);
    }

    /**
     * @param mixed[] $data
     */
    #[AsTwigFunction(name: 'emsco_webhook')]
    public function dispatchWebhook(string $eventName, array $data = []): void
    {
        $this->dispatcher->dispatch(new DispatchToWebhookEvent($eventName, $data));
    }

    #[AsTwigFunction(name: 'emsco_generate_email')]
    public function emailGenerate(string $title): Email
    {
        $mail = new Email();
        $mail->subject($title);

        return $mail;
    }

    #[AsTwigFunction(name: 'emsco_send_email')]
    public function emailSend(Email $email): void
    {
        $this->mailer->sendMail($email);
    }

    /**
     * @param array<mixed> $context
     */
    #[AsTwigFilter(name: 'emsco_debug')]
    public function logDebug(string $message, array $context = []): void
    {
        $context['twig'] = 'twig';
        $this->logger->debug($message, $context);
    }

    #[AsTwigFilter(name: 'emsco_log_error')]
    #[AsTwigFunction(name: 'emsco_error')]
    public function logError(string $error): void
    {
        $this->logger->error($error);
    }

    #[AsTwigFilter(name: 'emsco_log_notice')]
    #[AsTwigFunction(name: 'emsco_notice')]
    public function logNotice(string $notice): void
    {
        $this->logger->notice($notice);
    }

    #[AsTwigFilter(name: 'emsco_log_warning')]
    #[AsTwigFunction(name: 'emsco_warning')]
    public function logWarning(string $warning): void
    {
        $this->logger->warning($warning);
    }
}
