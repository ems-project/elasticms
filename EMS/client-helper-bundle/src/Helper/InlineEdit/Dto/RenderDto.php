<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Helper\InlineEdit\Dto;

use EMS\CommonBundle\Common\Bridge\Core\CoreBridgeResponse;

class RenderDto
{
    private const string DEFAULT_TITLE = 'Inline Editor';
    public string $closeUrl;
    /** @var array<mixed> */
    public array $documents = [];
    /** @var string[] */
    public array $elements = [];

    public function __construct(
        RenderPayloadDto $payload,
        ?CoreBridgeResponse $infoDocuments
    ) {
        /** @var array<string, array<mixed>> $infos */
        $infos = $infoDocuments?->response() ?? [];
        $this->closeUrl = $payload->url;

        foreach ($infos as $info) {
            $emsLink = $info['emsLink'];
            $elements = $payload->getElementsByEmsLink($emsLink);

            foreach ($elements as $element) {
                $this->elements[] = $element['selector'];
            }

            $this->documents[] = [
                'label' => \sprintf('%s: %s', $info['contentType']['singularName'], $info['label']),
                'elements' => $elements,
                'info' => $info,
            ];
        }
    }

    public function getTitle(): string
    {
        foreach ($this->documents as $document) {
            $elements = $document['elements'];
            $hasH1 = [] !== \array_filter($elements, fn (array $e) => 'h1' === $e['tag']);

            if ($hasH1) {
                return $document['label'];
            }
        }

        return self::DEFAULT_TITLE;
    }
}
