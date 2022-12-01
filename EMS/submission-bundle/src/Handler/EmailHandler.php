<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\EmailRequest;
use EMS\SubmissionBundle\Response\EmailHandleResponse;
use EMS\SubmissionBundle\Twig\TwigRenderer;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

final class EmailHandler extends AbstractHandler
{
    public function __construct(private readonly Mailer $mailer, private readonly TwigRenderer $twigRenderer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpoint($handleRequest);
            $message = $this->twigRenderer->renderMessageJSON($handleRequest);

            $emailRequest = new EmailRequest($endpoint, $message);

            $message = (new Email())
                ->subject($emailRequest->getSubject())
                ->from($emailRequest->getFrom())
                ->to($emailRequest->getEndpoint())
                ->html($emailRequest->getBody());

            $this->addAttachments($emailRequest, $message);

            $this->mailer->send($message);
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. %s', $exception->getMessage()));
        }

        return new EmailHandleResponse($message);
    }

    private function addAttachments(EmailRequest $emailRequest, Email $message): void
    {
        foreach ($emailRequest->getAttachments() as $attachment) {
            $filename = $attachment['originalName'] ?? $attachment['filename'] ?? null;
            $mimeType = $attachment['mimeType'] ?? null;

            if (null === $filename || null === $mimeType) {
                continue;
            }

            if (isset($attachment['base64'])) {
                $data = \base64_decode((string) $attachment['base64']);
                $message->attach($data, $filename, $mimeType);
            } elseif (isset($attachment['pathname'])) {
                $message->attachFromPath($attachment['pathname'], $filename, $mimeType);
            }
        }
    }
}
