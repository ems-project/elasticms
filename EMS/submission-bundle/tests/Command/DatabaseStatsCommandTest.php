<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Command;

use EMS\SubmissionBundle\Dto\FormSubmissionsCountDto;
use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class DatabaseStatsCommandTest extends KernelTestCase
{
    private MockObject $repository;
    private Application $application;
    private MessageLoggerListener $messageLogger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(FormSubmissionRepository::class);

        $kernel = self::bootKernel();
        $kernel->getContainer()->set('emss.repository.form_submission', $this->repository);

        $this->application = new Application($kernel);

        /** @var MessageLoggerListener $messageLogger */
        $messageLogger = $kernel->getContainer()->get('functional_test.message_listener');
        $this->messageLogger = $messageLogger;
    }

    public function testExecute()
    {
        $countDto = new FormSubmissionsCountDto('+1 month');
        $countDto->setWaiting(1);
        $countDto->setFailed(2);
        $countDto->setProcessed(3);
        $countDto->setTotal(6);

        $this->repository->expects($this->any())->method('getCounts')->willReturn($countDto);

        $command = $this->application->find('emss:database:stats');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'formName' => 'test',
            '--instance' => 'test',
            '--period' => '+1 month',
            '--email-to' => 'test@test.be',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString((string) $countDto->waiting, $output);
        $this->assertStringContainsString((string) $countDto->failed, $output);
        $this->assertStringContainsString((string) $countDto->processed, $output);
        $this->assertStringContainsString((string) $countDto->total, $output);
        $this->assertStringContainsString('Send stats email to: test@test.be', $output);

        /** @var Email $email */
        $email = $this->messageLogger->getEvents()->getMessages()[0];

        $this->assertEquals(['test@test.be'], \array_map(fn (Address $a) => $a->toString(), $email->getTo()));
        $this->assertEquals(['"elasticms form submissions" <noreply@elasticms.eu>'], \array_map(fn (Address $a) => $a->toString(), $email->getFrom()));
        $this->assertEquals('submission stats', $email->getSubject());

        $body = $email->getBody()->bodyToString();
        $this->assertStringContainsString((string) $countDto->waiting, $body);
        $this->assertStringContainsString((string) $countDto->failed, $body);
        $this->assertStringContainsString((string) $countDto->processed, $body);
        $this->assertStringContainsString((string) $countDto->total, $body);
    }
}
