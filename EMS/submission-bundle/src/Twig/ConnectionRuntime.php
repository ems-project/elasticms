<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use EMS\SubmissionBundle\Connection\Transformer;
use Twig\Extension\RuntimeExtensionInterface;

final class ConnectionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly Transformer $transformer)
    {
    }

    public function transform(string $content): string
    {
        return $this->transformer->transform(\explode('%.%', $content));
    }
}
