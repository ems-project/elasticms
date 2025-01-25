<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Contracts\Generator\Pdf\PdfGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;

final readonly class PdfController
{
    public function __construct(
        private Handler $handler,
        private PdfGeneratorInterface $pdfGenerator
    ) {
    }

    public function __invoke(EmschRequest $request): Response
    {
        $html = $this->handler->handle($request)->render();
        $pdfOptions = $this->pdfGenerator->createOptionsFromHtml($html);

        if ($request->isSubRequest()) {
            return $this->pdfGenerator->generateResponseFromHtml($html, $pdfOptions);
        }

        return $this->pdfGenerator->generateStreamedResponseFromHtml($html, $pdfOptions);
    }
}
