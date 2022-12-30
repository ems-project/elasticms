<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Revision;

use EMS\CommonBundle\Common\Document;
use EMS\CommonBundle\Service\Pdf\Pdf;
use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\CommonBundle\Service\Pdf\PdfPrintOptions;
use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Entity\Template;
use EMS\CoreBundle\Form\Field\RenderOptionType;
use EMS\CoreBundle\Repository\TemplateRepository;
use EMS\CoreBundle\Service\EnvironmentService;
use EMS\CoreBundle\Service\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment as Twig;

class ActionController
{
    public function __construct(
        private readonly TemplateRepository $templateRepository,
        private readonly EnvironmentService $environmentService,
        private readonly SearchService $searchService,
        private readonly PdfPrinterInterface $pdfPrinter,
        private readonly LoggerInterface $logger,
        private readonly Twig $twig,
    ) {
    }

    public function render(
        string $environmentName,
        int $templateId,
        string $ouuid,
        bool $_download,
        bool $public): Response
    {
        $action = $this->templateRepository->getById($templateId);
        if ($public && !$action->isPublic()) {
            throw new NotFoundHttpException('Template type not found');
        }

        $environment = $this->environmentService->giveByName($environmentName);
        $document = $this->searchService->get($environment, $action->giveContentType(), $ouuid);

        $body = $this->twig->createTemplate($action->getBody());

        if ($_download || !$action->getPreview()) {
            try {
                $content = $body->render([
                    'environment' => $environment,
                    'contentType' => $action->getContentType(),
                    'object' => $document,
                    'source' => $document->getSource(),
                    '_download' => $_download,
                ]);
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
                $content = 'Error in template';
            }
            $filename = $this->generateFilename($action, $environment, $document, $_download);

            if (RenderOptionType::PDF === $action->getRenderOption()) {
                return $this->generatePdfResponse($action, $filename, $content);
            }
            if (RenderOptionType::EXPORT === $action->getRenderOption()) {
                return $this->generateExportResponse($action, $filename, $content);
            }
        }

        return new Response($this->twig->render('@EMSCore/data/custom-view.html.twig', [
            'template' => $action,
            'environment' => $environment,
            'contentType' => $action->getContentType(),
            'object' => $document,
            'source' => $document->getSource(),
            '_download' => true,
            'body' => $body,
        ]));
    }

    private function generatePdfResponse(Template $action, string $filename, string $content): Response
    {
        $pdf = new Pdf($filename, $content);
        $printOptions = new PdfPrintOptions([
            PdfPrintOptions::ATTACHMENT => PdfPrintOptions::ATTACHMENT === $action->getDisposition(),
            PdfPrintOptions::COMPRESS => true,
            PdfPrintOptions::HTML5_PARSING => true,
            PdfPrintOptions::ORIENTATION => $action->getOrientation() ?? 'portrait',
            PdfPrintOptions::SIZE => $action->getSize() ?? 'A4',
        ]);

        return $this->pdfPrinter->getStreamedResponse($pdf, $printOptions);
    }

    private function generateExportResponse(Template $action, string $filename, string $content): Response
    {
        $headers = [];
        if (null !== $action->getMimeType()) {
            $headers['Content-Type'] = $action->getMimeType();
        }

        if (null !== $action->getDisposition()) {
            $attachment = 'inline' == $action->getDisposition() ?
                ResponseHeaderBag::DISPOSITION_INLINE :
                ResponseHeaderBag::DISPOSITION_ATTACHMENT;
            $extension = ($action->getExtension() ? '.'.$action->getExtension() : '');
            $headers['Content-Disposition'] = \sprintf('%s;filename="%s.%s"', $attachment, $filename, $extension);
        }
        if (null != $action->getAllowOrigin()) {
            $headers['Access-Control-Allow-Origin'] = $action->getAllowOrigin();
            $headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization, Accept, Accept-Language, If-None-Match, If-Modified-Since';
            $headers['Access-Control-Allow-Methods'] = 'GET, HEAD, OPTIONS';
        }

        return new Response($content, Response::HTTP_OK, $headers);
    }

    private function generateFilename(Template $action, Environment $environment, Document $document, bool $_download): string
    {
        $template = $action->getFilename();
        $template ??= (RenderOptionType::PDF === $action->getRenderOption() ? 'document.pdf' : $document->getOuuid());

        $twigTemplate = $this->twig->createTemplate($template);

        try {
            $filename = $twigTemplate->render([
                'environment' => $environment,
                'contentType' => $action->getContentType(),
                'object' => $document,
                'source' => $document->getSource(),
                '_download' => $_download,
            ]);
            $filename = \preg_replace('~[\r\n]+~', '', $filename);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }

        return $filename ?? 'error-in-filename-template';
    }
}
