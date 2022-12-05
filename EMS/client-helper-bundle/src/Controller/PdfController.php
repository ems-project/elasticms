<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Controller;

use EMS\ClientHelperBundle\Helper\Request\EmschRequest;
use EMS\ClientHelperBundle\Helper\Request\Handler;
use EMS\CommonBundle\Contracts\Generator\Pdf\PdfGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PdfController
{
    public function __construct(private readonly Handler $handler, private readonly Environment $templating, private readonly PdfGeneratorInterface $pdfGenerator)
    {
    }

    public function __invoke(EmschRequest $request): Response
    {
        $result = $this->handler->handle($request);
        $html = $this->templating->render($result['template'], $result['context']);
        $pdfOptions = $this->pdfGenerator->createOptionsFromHtml($html);

        if ($request->isSubRequest()) {
            return $this->pdfGenerator->generateResponseFromHtml($html, $pdfOptions);
        }

        return $this->pdfGenerator->generateStreamedResponseFromHtml($html, $pdfOptions);
    }
}
