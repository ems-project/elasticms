<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ConnectionExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('emss_skip_submit', [ConnectionRuntime::class, 'skipSubmitException'], ['is_safe' => ['html']]),
        ];
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('emss_connection', [ConnectionRuntime::class, 'transform'], ['is_safe' => ['html']]),
        ];
    }
}
