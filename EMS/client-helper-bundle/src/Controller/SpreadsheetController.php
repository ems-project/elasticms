<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Contracts\SpreadsheetGeneratorServiceInterface;
use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class SpreadsheetController
{
    public function __construct(private readonly Handler $handler, private readonly Environment $templating, private readonly SpreadsheetGeneratorServiceInterface $spreadsheetGenerator)
    {
    }

    public function __invoke(EmschRequest $request): Response
    {
        $result = $this->handler->handle($request);
        $config = Json::decode($this->templating->render($result['template'], $result['context']));
        if ($request->isSubRequest()) {
            return $this->spreadsheetGenerator->generateSpreadsheetCacheableResponse($config);
        }

        return $this->spreadsheetGenerator->generateSpreadsheet($config);
    }
}
