<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Handler;

use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\FormBundle\Submission\AbstractHandler;
use EMS\FormBundle\Submission\FailedHandleResponse;
use EMS\FormBundle\Submission\HandleRequestInterface;
use EMS\FormBundle\Submission\HandleResponseInterface;
use EMS\SubmissionBundle\Request\PdfRequest;
use EMS\SubmissionBundle\Response\PdfHandleResponse;
use EMS\SubmissionBundle\Response\ResponseTransformer;
use EMS\SubmissionBundle\Twig\TwigRenderer;

final class PdfHandler extends AbstractHandler
{
    public function __construct(private readonly PdfPrinterInterface $pdfPrinter, private readonly TwigRenderer $twigRenderer, private readonly ResponseTransformer $responseTransformer)
    {
    }

    public function handle(HandleRequestInterface $handleRequest): HandleResponseInterface
    {
        try {
            $endpoint = $this->twigRenderer->renderEndpointJSON($handleRequest);
            $html = $this->twigRenderer->renderMessageBlock($handleRequest, 'pdfHtml') ?? '';

            $pdfRequest = new PdfRequest($endpoint, $html);
            $pdfOutput = $this->pdfPrinter->getPdfOutput($pdfRequest->getPdf(), $pdfRequest->getPdfOptions());

            $handleResponse = new PdfHandleResponse($pdfRequest, $pdfOutput);

            return $this->responseTransformer->transform($handleRequest, $handleResponse);
        } catch (\Throwable $exception) {
            return new FailedHandleResponse($exception);
        }
    }
}
