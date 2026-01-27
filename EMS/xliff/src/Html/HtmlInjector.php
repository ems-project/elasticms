<?php

declare(strict_types=1);

namespace EMS\Xliff\Html;

use EMS\Helpers\Html\HtmlHelper;
use EMS\Xliff\Model\UnitGroup;

class HtmlInjector
{
    public function inject(
        UnitGroup $document,
        string $locale
    ): string
    {
        $document = new \DOMDocument();
        $html = new \DOMElement('html');
        $document->appendChild($html);
        $body = new \DOMElement('body');
        $html->appendChild($body);
//        $this->groupToHtmlNodes($group, $nodeName, $body, $namespaces);

        $html = '';
        foreach ($body->childNodes as $node) {
            $html .= $document->saveXML($node);
        }

        return HtmlHelper::prettyPrint($html);
    }
}
