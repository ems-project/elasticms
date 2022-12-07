<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Dto;

final class FormSubmissionsCountDto
{
    /** @var int */
    public $failed = 0;
    /** @var int */
    public $processed = 0;
    /** @var int */
    public $waiting = 0;
    /** @var int */
    public $total = 0;
    /** @var int */
    public $periodFailed = 0;
    /** @var int */
    public $periodProcessed = 0;
    /** @var int */
    public $periodWaiting = 0;
    /** @var int */
    public $periodTotal = 0;

    public function __construct(public string $period)
    {
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function toArray(): array
    {
        return [
            ['total failed processing', $this->failed],
            ['total waiting for processing', $this->waiting],
            ['total processed', $this->processed],
            ['total submissions', $this->total],
        ];
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function toArrayPeriod(): array
    {
        return [
            [\sprintf('total failed processing (last %s)', $this->period), $this->periodFailed],
            [\sprintf('total waiting for processing (last %s)', $this->period), $this->periodWaiting],
            [\sprintf('total processed (last %s)', $this->period), $this->periodProcessed],
            [\sprintf('total submissions (last %s)', $this->period), $this->periodTotal],
        ];
    }

    public function setFailed(int $failed): void
    {
        $this->failed = $failed;
    }

    public function setWaiting(int $waiting): void
    {
        $this->waiting = $waiting;
    }

    public function setProcessed(int $processed): void
    {
        $this->processed = $processed;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function setPeriodFailed(int $periodFailed): void
    {
        $this->periodFailed = $periodFailed;
    }

    public function setPeriodProcessed(int $periodProcessed): void
    {
        $this->periodProcessed = $periodProcessed;
    }

    public function setPeriodWaiting(int $periodWaiting): void
    {
        $this->periodWaiting = $periodWaiting;
    }

    public function setPeriodTotal(int $periodTotal): void
    {
        $this->periodTotal = $periodTotal;
    }
}
