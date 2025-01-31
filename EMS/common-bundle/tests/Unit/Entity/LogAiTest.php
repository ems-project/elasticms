<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Entity;

use EMS\CommonBundle\Entity\Log;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class LogAiTest extends TestCase
{
    private Log $log;

    #[\Override]
    protected function setUp(): void
    {
        $this->log = new Log();
    }

    public function testId(): void
    {
        $uuid = Uuid::uuid4();
        $this->log->setId($uuid);

        $this->assertSame($uuid->toString(), $this->log->getId());
    }

    public function testMessage(): void
    {
        $message = 'Test message';
        $this->log->setMessage($message);

        $this->assertSame($message, $this->log->getMessage());
    }

    public function testContext(): void
    {
        $context = ['key' => 'value'];
        $this->log->setContext($context);

        $this->assertSame($context, $this->log->getContext());
    }

    // ... Continue with other public methods ...

    public function testUpdateModified(): void
    {
        $this->log->updateModified();
        $modified = $this->log->getModified();

        $this->assertInstanceOf(\DateTime::class, $modified);
        $this->assertLessThanOrEqual(new \DateTime(), $modified);
    }

    public function testOuuid(): void
    {
        $ouuid = 'test-ouuid';
        $this->log->setOuuid($ouuid);

        $this->assertSame($ouuid, $this->log->getOuuid());
    }

    public function testLevel(): void
    {
        $level = 200;
        $this->log->setLevel($level);

        $this->assertSame($level, $this->log->getLevel());
    }

    public function testLevelName(): void
    {
        $levelName = 'INFO';
        $this->log->setLevelName($levelName);

        $this->assertSame($levelName, $this->log->getLevelName());
    }

    public function testChannel(): void
    {
        $channel = 'test-channel';
        $this->log->setChannel($channel);

        $this->assertSame($channel, $this->log->getChannel());
    }

    public function testExtra(): void
    {
        $extra = ['key' => 'extra-value'];
        $this->log->setExtra($extra);

        $this->assertSame($extra, $this->log->getExtra());
    }

    public function testFormatted(): void
    {
        $formatted = 'Formatted log message';
        $this->log->setFormatted($formatted);

        $this->assertSame($formatted, $this->log->getFormatted());
    }

    public function testUsername(): void
    {
        $username = 'test-user';
        $this->log->setUsername($username);

        $this->assertSame($username, $this->log->getUsername());
    }

    public function testImpersonator(): void
    {
        $impersonator = 'test-impersonator';
        $this->log->setImpersonator($impersonator);

        $this->assertSame($impersonator, $this->log->getImpersonator());
    }
}
