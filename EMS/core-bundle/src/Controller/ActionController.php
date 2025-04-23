<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class ActionController
{
    public function field(int $fieldId): JsonResponse
    {
        return new JsonResponse(['field' => $fieldId]);
    }
}