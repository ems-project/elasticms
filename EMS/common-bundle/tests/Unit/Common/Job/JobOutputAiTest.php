<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Job;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use EMS\CommonBundle\Common\Job\JobOutput;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobOutputAiTest extends TestCase
{
    private JobOutput $jobOutput;
    private AdminInterface $admin;
    private Job $job;
    private OutputInterface $otherOutput;

    #[\Override]
    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);
        $this->job = $this->createMock(Job::class);
        $this->otherOutput = $this->createMock(OutputInterface::class);

        $this->jobOutput = new JobOutput($this->admin, $this->job, $this->otherOutput);
    }

    public function testDoWrite(): void
    {
        $message = 'Test message';
        $newline = true;

        $this->admin->expects($this->once())->method('jobDoWrite')
            ->with($this->job, $message, $newline);

        $this->otherOutput->expects($this->once())->method('write')
            ->with($message, $newline);

        $this->jobOutput->doWrite($message, $newline);
    }

    public function testSetVerbosity(): void
    {
        $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        $this->jobOutput->setVerbosity($verbosity);

        $this->assertSame(OutputInterface::VERBOSITY_NORMAL, $this->jobOutput->getVerbosity());
    }
}
