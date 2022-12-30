<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller\Revision;

use EMS\CommonBundle\Service\Pdf\Pdf;
use EMS\CommonBundle\Service\Pdf\PdfPrinterInterface;
use EMS\CommonBundle\Service\Pdf\PdfPrintOptions;
use EMS\CoreBundle\EMSCoreBundle;
use EMS\CoreBundle\Entity\Environment;
use EMS\CoreBundle\Entity\Template;
use EMS\CoreBundle\Form\Field\RenderOptionType;
use EMS\CoreBundle\Repository\EnvironmentRepository;
use EMS\CoreBundle\Repository\TemplateRepository;
use EMS\CoreBundle\Service\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as Twig;
use Twig\Environment as TwigEnvironment;
use Twig\Error\Error;

class ActionController
{
    public function __construct(
        private readonly TemplateRepository $templateRepository,
        private readonly EnvironmentRepository $environmentRepository,
        private readonly SearchService $searchService,
        private readonly PdfPrinterInterface $pdfPrinter,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
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
        /** @var Template|null $template * */
        $template = $this->templateRepository->find($templateId);

        if (null === $template || ($public && !$template->isPublic())) {
            throw new NotFoundHttpException('Template type not found');
        }

        $environment = $this->environmentRepository->findBy([
            'name' => $environmentName,
        ]);

        if (!$environment || 1 != \count($environment)) {
            throw new NotFoundHttpException('Environment type not found');
        }

        /** @var Environment $environment */
        $environment = $environment[0];

        $document = $this->searchService->get($environment, $template->giveContentType(), $ouuid);

        try {
            $body = $this->twig->createTemplate($template->getBody());
        } catch (Error $e) {
            $this->logger->error('log.template.twig.error', [
                'template_id' => $template->getId(),
                'template_name' => $template->getName(),
                'template_label' => $template->getLabel(),
                'error_message' => $e->getMessage(),
            ]);
            $body = $this->twig->createTemplate($this->translator->trans('log.template.twig.error', [
                '%template_id%' => $template->getId(),
                '%template_name%' => $template->getName(),
                '%template_label%' => $template->getLabel(),
                '%error_message%' => $e->getMessage(),
            ], EMSCoreBundle::TRANS_DOMAIN));
        }

        if (RenderOptionType::PDF === $template->getRenderOption() && ($_download || !$template->getPreview())) {
            $output = $body->render([
                'environment' => $environment,
                'contentType' => $template->getContentType(),
                'object' => $document,
                'source' => $document->getSource(),
                '_download' => true,
            ]);
            $filename = $this->generateFilename($this->twig, $template->getFilename() ?? 'document.pdf', [
                'environment' => $environment,
                'contentType' => $template->getContentType(),
                'object' => $document,
                'source' => $document->getSource(),
                '_download' => true,
            ]);

            $pdf = new Pdf($filename, $output);
            $printOptions = new PdfPrintOptions([
                PdfPrintOptions::ATTACHMENT => PdfPrintOptions::ATTACHMENT === $template->getDisposition(),
                PdfPrintOptions::COMPRESS => true,
                PdfPrintOptions::HTML5_PARSING => true,
                PdfPrintOptions::ORIENTATION => $template->getOrientation() ?? 'portrait',
                PdfPrintOptions::SIZE => $template->getSize() ?? 'A4',
            ]);

            return $this->pdfPrinter->getStreamedResponse($pdf, $printOptions);
        }
        if ($_download || (0 === \strcmp($template->getRenderOption(), RenderOptionType::EXPORT) && !$template->getPreview())) {
            if (null != $template->getMimeType()) {
                \header('Content-Type: '.$template->getMimeType());
            }

            $filename = $this->generateFilename($this->twig, $template->getFilename() ?? $ouuid, [
                'environment' => $environment,
                'contentType' => $template->getContentType(),
                'object' => $document,
                'source' => $document->getSource(),
            ]);

            if (!empty($template->getDisposition())) {
                $attachment = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
                if ('inline' == $template->getDisposition()) {
                    $attachment = ResponseHeaderBag::DISPOSITION_INLINE;
                }
                \header("Content-Disposition: $attachment; filename=".$filename.($template->getExtension() ? '.'.$template->getExtension() : ''));
            }
            if (null != $template->getAllowOrigin()) {
                \header('Access-Control-Allow-Origin: '.$template->getAllowOrigin());
                \header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept, Accept-Language, If-None-Match, If-Modified-Since');
                \header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
            }

            $output = $body->render([
                'environment' => $environment,
                'contentType' => $template->getContentType(),
                'object' => $document,
                'source' => $document->getSource(),
            ]);
            echo $output;

            exit;
        }

        return new Response($this->twig->render('@EMSCore/data/custom-view.html.twig', [
            'template' => $template,
            'object' => $document,
            'environment' => $environment,
            'contentType' => $template->getContentType(),
            'body' => $body,
        ]));
    }

    /**
     * @param array<string, mixed> $options
     */
    private function generateFilename(TwigEnvironment $twig, string $rawTemplate, array $options): string
    {
        try {
            $template = $twig->createTemplate($rawTemplate);
            $filename = $template->render($options);
            $filename = \preg_replace('~[\r\n]+~', '', $filename);
        } catch (\Throwable) {
            $filename = null;
        }

        return $filename ?? 'error-in-filename-template';
    }
}
