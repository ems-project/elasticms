<?php

namespace EMS\CommonBundle\Tests\Unit\Common;

use EMS\CommonBundle\Common\EMSLink;
use PHPUnit\Framework\TestCase;

class EMSLinkAiTest extends TestCase
{
    public function testFromContentTypeOuuid(): void
    {
        $link = EMSLink::fromContentTypeOuuid('page', 'AWTLzKLc8K-kdP4iJ3rt');
        $this->assertEquals('ems://object:page:AWTLzKLc8K-kdP4iJ3rt', (string) $link);
    }

    public function testIsValid(): void
    {
        $link = EMSLink::fromContentTypeOuuid('page', 'AWTLzKLc8K-kdP4iJ3rt');
        $this->assertTrue($link->isValid());
    }

    public function testFromText(): void
    {
        $link = EMSLink::fromText('ems://object:page:AWTLzKLc8K-kdP4iJ3rt');
        $this->assertEquals('ems://object:page:AWTLzKLc8K-kdP4iJ3rt', (string) $link);
    }

    public function testFromMatch(): void
    {
        $match = [
            'ouuid' => 'AWTLzKLc8K-kdP4iJ3rt',
            'link_type' => 'object',
            'content_type' => 'page',
        ];
        $link = EMSLink::fromMatch($match);
        $this->assertEquals('ems://object:page:AWTLzKLc8K-kdP4iJ3rt', (string) $link);
    }

    public function testFromDocument(): void
    {
        $document = [
            '_id' => 'AWTLzKLc8K-kdP4iJ3rt',
            '_source' => [
                '_contenttype' => 'page',
            ],
        ];
        $link = EMSLink::fromDocument($document);
        $this->assertEquals('ems://object:page:AWTLzKLc8K-kdP4iJ3rt', (string) $link);
    }

    public function testGetters(): void
    {
        $link = EMSLink::fromText('ems://object:page:AWTLzKLc8K-kdP4iJ3rt');
        $this->assertEquals('object', $link->getLinkType());
        $this->assertEquals('page', $link->getContentType());
        $this->assertEquals('AWTLzKLc8K-kdP4iJ3rt', $link->getOuuid());
        $this->assertEquals([], $link->getQuery());
        $this->assertTrue($link->hasContentType());
        $this->assertEquals('page:AWTLzKLc8K-kdP4iJ3rt', $link->getEmsId());
    }

    public function testJsonSerialize(): void
    {
        $link = EMSLink::fromText('ems://object:page:AWTLzKLc8K-kdP4iJ3rt');
        $this->assertEquals('ems://object:page:AWTLzKLc8K-kdP4iJ3rt', $link->jsonSerialize());
    }

    public function testFromMatchException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ouuid is required!');

        $match = [
            'link_type' => 'object',
            'content_type' => 'page',
        ];
        EMSLink::fromMatch($match);
    }
}
