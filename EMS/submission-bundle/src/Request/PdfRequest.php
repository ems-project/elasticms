<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Request;

use EMS\CommonBundle\Service\Pdf\Pdf;
use EMS\CommonBundle\Service\Pdf\PdfInterface;
use EMS\CommonBundle\Service\Pdf\PdfPrintOptions;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PdfRequest extends AbstractRequest
{
    /** @var array{filename: string, orientation: string, size: string} */
    protected $endpoint;

    /** @var string */
    private $html;

    /**
     * @param array<string, mixed> $endpoint
     */
    public function __construct(array $endpoint, string $html)
    {
        /** @var array{filename: string, orientation: string, size: string} $endpoint */
        $endpoint = $this->resolveEndpoint($endpoint);

        $this->endpoint = $endpoint;
        $this->html = $html;
    }

    public function getFilename(): string
    {
        return $this->endpoint['filename'];
    }

    public function getPdf(): PdfInterface
    {
        return new Pdf($this->getFilename(), $this->html);
    }

    public function getPdfOptions(): PdfPrintOptions
    {
        return new PdfPrintOptions([
            PdfPrintOptions::ORIENTATION => $this->endpoint[PdfPrintOptions::ORIENTATION],
            PdfPrintOptions::SIZE => $this->endpoint[PdfPrintOptions::SIZE],
        ]);
    }

    protected function getEndpointOptionResolver(): OptionsResolver
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'filename' => 'handle.pdf',
            PdfPrintOptions::ORIENTATION => 'portrait',
            PdfPrintOptions::SIZE => 'a4',
        ]);

        return $optionsResolver;
    }
}
