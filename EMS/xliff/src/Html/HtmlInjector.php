<?php

declare(strict_types=1);

namespace EMS\Xliff\Html;

use EMS\Helpers\Html\HtmlHelper;
use EMS\Helpers\Standard\Type;
use EMS\Xliff\Model\Inline\Group;
use EMS\Xliff\Model\Inline\Node;
use EMS\Xliff\Model\Inline\PairedCode;
use EMS\Xliff\Model\Inline\Placeholder;
use EMS\Xliff\Model\Inline\Text;
use EMS\Xliff\Model\Note;
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
        if (null !== $unitGroup->type) {
            $parent = DomHelper::createElement($parent, $unitGroup->type, $this->notesToAttributes($unitGroup->getNotes()));
        }
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
        if (null !== $unit->type) {
            $parent = DomHelper::createElement($parent, $unit->type, $this->notesToAttributes($unit->getNotes()));
        }
        foreach ($unit->getSegments() as $segment) {
            $nodes = match ($segmentChildTag) {
                'source' => $segment->getSourceNodes(),
                'target' => $segment->getTargetNodes(),
                default => throw new \RuntimeException(\sprintf('Unit segment %s not supported', $segmentChildTag)),
            };
            foreach ($nodes as $node) {
                $this->nodeToHtmlDom($parent, $node);
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
        if (!\in_array($node->equivalentText, [null, ' '], true)) {
            DomHelper::createElementFromString($dom, $node->equivalentText, $node->type);
        } else {
            DomHelper::createElement($dom, $node->type);
        }
    }

    private function appendGroup(\DOMNode $dom, Group $node): void
    {
        if (null !== $node->equivalentOpeningText) {
            $element = DomHelper::createElementFromString($dom, \sprintf('%s</%s>', $node->equivalentOpeningText, $node->type), $node->type);
        } else {
            $element = DomHelper::createElement($dom, $node->type);
        }
        foreach ($node->getChildren() as $child) {
            $this->nodeToHtmlDom($element, $child);
        }
    }

    private function nodeToHtmlDom(\DOMElement $parent, Node $node): void
    {
        match ($node::class) {
            Text::class => $this->appendText($parent, $node),
            Placeholder::class => $this->appendPlaceholder($parent, $node),
            Group::class => $this->appendGroup($parent, $node),
            PairedCode::class => $this->appendPairedCode($parent, $node),
            default => throw new \RuntimeException(\sprintf('Unit segment node %s not supported', $node::class)),
        };
    }

    /**
     * @param  Note[]                $getNotes
     * @return array<string, string>
     */
    private function notesToAttributes(array $getNotes): array
    {
        $attributes = [];
        foreach ($getNotes as $note) {
            if (null === $note->from) {
                continue;
            }
            $attributes[$note->from] = Type::string($note->text);
        }

        return $attributes;
    }

    private function appendPairedCode(\DOMElement $parent, PairedCode $node): void
    {
        $pairedCode = DomHelper::createElementFromString($parent, $node->equivalentOpeningText.$node->equivalentClosingText, $node->resourceName);
        foreach ($node->getChildren() as $child) {
            $this->nodeToHtmlDom($pairedCode, $child);
        }
    }
}
