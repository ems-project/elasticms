<?php

declare(strict_types=1);

namespace EMS\Xliff\Tests\Unit\Xliff;

use EMS\Xliff\Html\HtmlExtractor;
use EMS\Xliff\Id\SequentialIdGenerator;
use EMS\Xliff\Model\Inline\PairedCode;
use EMS\Xliff\Model\Inline\Text;
use PHPUnit\Framework\TestCase;

class HtmlExtractorTest extends TestCase
{
    public function testEmptyHtml(): void
    {
        $extractor = new HtmlExtractor(new SequentialIdGenerator());
        $group = $extractor->extract('[body]', '');
        $this->assertCount(0, $group->getNodes());
        $this->assertSame('[body]', $group->resourceName);
        $group = $extractor->extract('[body]', '<p>coucou</p>');
        $this->assertCount(1, $group->getNodes());
        $unit = $group->getNodes()[0];
        $this->assertCount(1, $unit->getSegments());
        $segment = $unit->getSegments()[0];
        $this->assertSame('/html/body/p', $unit->resourceName);
        $source = $segment->getSourceNodes()[0];
        $this->assertInstanceOf(Text::class, $source);
        $this->assertSame('coucou', \trim($source->text));
        $group = $extractor->extract('[body]', '<p>coucou ceci <a href="/index.html">est un liens</a>.</p>');
        $segment = $group->getNodes()[0]->getSegments()[0];
        $this->assertCount(3, $segment->getSourceNodes());
        $link = $segment->getSourceNodes()[1];
        $this->assertInstanceOf(PairedCode::class, $link);
        $this->assertSame('est un liens', $link->getChildren()[0]->text);
    }
}
