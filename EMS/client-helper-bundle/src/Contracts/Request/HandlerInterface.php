<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Contracts\Request;

use Symfony\Component\HttpFoundation\Request;

interface HandlerInterface
{
    /**
     * @return array<mixed>
     */
    public function handle(Request $request): array;
}
