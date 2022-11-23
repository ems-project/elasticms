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
    /** @var PdfPrinterInterface */
    private $pdfPrinter;
    /** @var TwigRenderer */
    private $twigRenderer;
    /** @var ResponseTransformer */
    private $responseTransformer;

    public function __construct(
        PdfPrinterInterface $pdfPrinter,
        TwigRenderer $twigRenderer,
        ResponseTransformer $responseTransformer
    ) {
        $this->pdfPrinter = $pdfPrinter;
        $this->twigRenderer = $twigRenderer;
        $this->responseTransformer = $responseTransformer;
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
        } catch (\Exception $exception) {
            return new FailedHandleResponse(\sprintf('Submission failed, contact your admin. (%s)', $exception->getMessage()));
        }
    }
}
