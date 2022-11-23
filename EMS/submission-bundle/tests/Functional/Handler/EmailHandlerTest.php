<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler;

use EMS\FormBundle\Submission\AbstractHandler;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class EmailHandlerTest extends AbstractHandlerTest
{
    private MessageLoggerListener $messageLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageLogger = $this->container->get('functional_test.message_listener');
    }

    protected function getHandler(): AbstractHandler
    {
        return $this->container->get('functional_test.emss.handler.email');
    }

    public function testSubmitFormData(): void
    {
        $endpoint = '{{ data.email }}';
        $message = \json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test submission',
            'body' => 'Hi my name is {{ data.first_name }} {{ data.last_name }}',
        ]);

        $response = $this->handle($this->createForm(), $endpoint, $message)->getResponse();

        /** @var Email $email */
        $email = $this->messageLogger->getEvents()->getMessages()[0];

        $this->assertEquals(['user1@test.test'], \array_map(fn (Address $a) => $a->toString(), $email->getTo()));
        $this->assertEquals(['noreply@test.test'], \array_map(fn (Address $a) => $a->toString(), $email->getFrom()));
        $this->assertEquals('Test submission', $email->getSubject());
        $this->assertEquals('Hi my name is testFirstName testLastName', $email->getHtmlBody());

        $this->assertEquals('{"status":"success","data":"Submission send by mail."}', $response);
    }

    public function testSubmitMultipleFiles(): void
    {
        $endpoint = 'test@example.com';
        $message = \file_get_contents(__DIR__.'/../fixtures/twig/message_email.twig');

        $response = $this->handle($this->createFormUploadFiles(), $endpoint, $message)->getResponse();

        /** @var Email $email */
        $email = $this->messageLogger->getEvents()->getMessages()[0];

        $attachments = $email->getAttachments();

        $this->assertEquals('text/plain disposition: attachment filename: attachment.txt', $attachments[0]->asDebugString());
        $this->assertEquals('Text example attachment', $attachments[0]->getBody());
        $this->assertEquals('text/plain disposition: attachment filename: attachment2.txt', $attachments[1]->asDebugString());
        $this->assertEquals('Text example attachment2', $attachments[1]->getBody());

        $this->assertEquals('{"status":"success","data":"Submission send by mail."}', $response);
    }

    public function testEmptyEndpoint(): void
    {
        $message = \json_encode([
            'from' => 'noreply@test.test',
            'subject' => 'Test submission',
            'body' => 'example',
        ]);

        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. Email \"\" does not comply with addr-spec of RFC 2822."}',
            $this->handle($this->createForm(), '', $message)->getResponse()
        );
    }

    public function testEmptyMessage(): void
    {
        $this->assertEquals(
            '{"status":"error","data":"Submission failed, contact your admin. From email address not defined."}',
            $this->handle($this->createForm(), 'user@example.com', '')->getResponse()
        );
    }
}
