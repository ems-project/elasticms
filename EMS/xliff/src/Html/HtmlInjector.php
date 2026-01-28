<?php

declare(strict_types=1);

namespace EMS\Xliff\Html;

use EMS\Helpers\Html\HtmlHelper;
use EMS\Xliff\Model\Inline\Placeholder;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Unit;
use EMS\Xliff\Model\UnitGroup;
use EMS\Xliff\XML\DomHelper;

class HtmlInjector
{
    public function inject(
        UnitGroup $unitGroup,
        string $segmentChildTag,
    ): string {
        $document = new \DOMDocument();
        $html = DomHelper::createElement($document, 'html');
        $body = DomHelper::createElement($html, 'body');
        $this->unitGroupToHtmlDom($unitGroup, $segmentChildTag, $body);

        $html = '';
        foreach ($body->childNodes as $node) {
            $html .= $document->saveXML($node);
        }

        return HtmlHelper::prettyPrint($html);
    }

    private function unitGroupToHtmlDom(UnitGroup $unitGroup, string $segmentChildTag, \DOMElement $parent): void
    {
        foreach ($unitGroup->getNodes() as $node) {
            match ($node::class) {
                UnitGroup::class => $this->unitGroupToHtmlDom($node, $segmentChildTag, $parent),
                Unit::class => $this->unitToHtmlDom($node, $segmentChildTag, $parent),
                default => throw new \RuntimeException(\sprintf('Unit group %s not supported', $node::class)),
            };
        }
    }

    private function unitToHtmlDom(Unit $unit, string $segmentChildTag, \DOMElement $parent): void
    {
        if (null == $unit->type) {
            $dom = new \DOMNode();
        } else {
            $dom = DomHelper::createElement($parent, $unit->type);
        }
        foreach ($unit->getSegments() as $segment) {
            $nodes = match ($segmentChildTag) {
                'source' => $segment->getSourceNodes(),
                'target' => $segment->getTargetNodes(),
                default => throw new \RuntimeException(\sprintf('Unit segment %s not supported', $segmentChildTag)),
            };
            foreach ($nodes as $node) {
                match ($node::class) {
                    Text::class => $this->appendText($dom, $node),
                    Placeholder::class => $this->appendPlaceholder($dom, $node),
                    default => throw new \RuntimeException(\sprintf('Unit segment node %s not supported', $node::class)),
                };
            }
        }
    }

    private function appendText(\DOMNode $dom, Text $node): void
    {
        $text = new \DOMText($node->text);
        $dom->appendChild($text);
    }

    private function appendPlaceholder(\DOMNode $dom, Placeholder $node): void
    {
        DomHelper::createElement($dom, $node->type);
    }
}
