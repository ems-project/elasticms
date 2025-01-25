<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Contracts\Request;

use EMS\CommonBundle\Contracts\Twig\TemplateInterface;
use Symfony\Component\HttpFoundation\Request;

interface HandlerInterface
{
    public function handle(Request $request): TemplateInterface;
}
