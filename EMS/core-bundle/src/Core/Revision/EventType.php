<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Revision;

class EventType
{
    private function __construct(private readonly bool $migrate = false, private readonly bool $finalize = false, private readonly bool $publish = false, private readonly bool $draft = false)
    {
    }

    public static function migrateEvent(): self
    {
        return new self(migrate: true, finalize: true);
    }

    public static function finalizeEvent(): self
    {
        return new self(finalize: true);
    }

    public static function publishEvent(): self
    {
        return new self(finalize: true, publish : true);
    }

    public static function draftEvent(): self
    {
        return new self(draft : true);
    }

    public function isMigrate(): bool
    {
        return $this->migrate;
    }

    public function isFinalize(): bool
    {
        return $this->finalize;
    }

    public function isPublish(): bool
    {
        return $this->publish;
    }

    public function isDraft(): bool
    {
        return $this->draft;
    }
}
