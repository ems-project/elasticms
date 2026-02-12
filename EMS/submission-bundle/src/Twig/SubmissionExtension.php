<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Twig;

use EMS\SubmissionBundle\Connection\Transformer;
use EMS\SubmissionBundle\Exception\SkipSubmissionException;
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Extension\AbstractExtension;

class SubmissionExtension extends AbstractExtension
{
    public function __construct(private readonly Transformer $transformer)
    {
    }

    #[AsTwigFilter(name: 'emss_connection', isSafe: ['html'])]
    public function transform(string $content): string
    {
        return $this->transformer->transform(\explode('%.%', $content));
    }

    #[AsTwigFunction(name: 'emss_skip_submit', isSafe: ['html'])]
    public function skipSubmitException(): never
    {
        throw new SkipSubmissionException();
    }
}
