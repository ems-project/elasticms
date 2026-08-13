<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\Job;

use EMS\CommonBundle\Common\CoreApi\Endpoint\Admin\Message\Job;
use EMS\CommonBundle\Common\Job\JobOutput;
use EMS\CommonBundle\Contracts\CoreApi\Endpoint\Admin\AdminInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class JobOutputAiTest extends TestCase
{
    private JobOutput $jobOutput;
    private AdminInterface $admin;
    /**
     * @var Stub&Job
     */
    private Stub $job;
    private OutputInterface $otherOutput;

    #[\Override]
    protected function setUp(): void
    {
        $this->admin = $this->createMock(AdminInterface::class);
        $this->job = $this->createStub(Job::class);
        $this->otherOutput = $this->createMock(OutputInterface::class);

        $this->jobOutput = new JobOutput($this->admin, $this->job, $this->otherOutput);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testDoWrite(): void
    {
        $message = 'Test message';

        $this->admin->expects($this->once())->method('jobDoWrite')
            ->with($this->job, $message, true);

        $this->otherOutput->expects($this->once())->method('write')
            ->with($message, true);

        $this->jobOutput->write($message, true);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetVerbosity(): void
    {
        $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        $this->jobOutput->setVerbosity($verbosity);

        $this->assertSame(OutputInterface::VERBOSITY_NORMAL, $this->jobOutput->getVerbosity());
    }
}
