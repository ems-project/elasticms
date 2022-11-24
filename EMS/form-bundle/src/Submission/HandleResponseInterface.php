<?php

declare(strict_types=1);

namespace EMS\FormBundle\Submission;

interface HandleResponseInterface
{
    public function getStatus(): string;

    public function getResponse(): string;

    /**
     * @return array{status: string, data: string, success: string}
     */
    public function getSummary(): array;
}
