<?php

declare(strict_types=1);

namespace EMS\FormBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('emsf_http_call', [EndpointRuntime::class, 'callHttpEndpoint']),
        ];
    }
}
