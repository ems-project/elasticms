<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ConnectionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('emss_skip_submit', [ConnectionRuntime::class, 'skipSubmitException'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('emss_connection', [ConnectionRuntime::class, 'transform'], ['is_safe' => ['html']]),
        ];
    }
}
