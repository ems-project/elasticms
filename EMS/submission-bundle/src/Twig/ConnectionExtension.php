<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class ConnectionExtension extends abstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('emss_connection', [ConnectionRuntime::class, 'transform'], ['is_safe' => ['html']]),
            new TwigFilter('emss_skip_submit', [ConnectionRuntime::class, 'skipSubmitException'], ['is_safe' => ['html']]),
        ];
    }
}
