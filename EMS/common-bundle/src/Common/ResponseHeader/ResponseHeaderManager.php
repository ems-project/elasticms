<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\ResponseHeader;

use Symfony\Component\HttpFoundation\RequestStack;

final readonly class ResponseHeaderManager
{
    private const string EMS_RESPONSE_HEADERS = '_ems_response_headers';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function getContext(): ResponseHeaderContext
    {
        $request = $this->requestStack->getCurrentRequest()
            ?? throw new \LogicException('No active HTTP request.');

        $context = $request->attributes->get(self::EMS_RESPONSE_HEADERS);

        if (!$context instanceof ResponseHeaderContext) {
            $context = new ResponseHeaderContext();
            $request->attributes->set(self::EMS_RESPONSE_HEADERS, $context);
        }

        return $context;
    }
}
