<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Contracts\SpreadsheetGeneratorServiceInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class SpreadsheetController
{
    public function __construct(
        private Handler $handler,
        private SpreadsheetGeneratorServiceInterface $spreadsheetGenerator
    ) {
    }

    public function __invoke(EmschRequest $request): Response
    {
        $config = $this->handler->handle($request)->json();

        if ($request->isSubRequest()) {
            return $this->spreadsheetGenerator->generateSpreadsheetCacheableResponse($config);
        }

        return $this->spreadsheetGenerator->generateSpreadsheet($config);
    }
}
