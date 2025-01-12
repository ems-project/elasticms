<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Filter;

use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use App\CLI\Client\WebToElasticms\Filter\Html\ClassCleaner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class ClassCleanerTest extends TestCase
{
    public function testClassCleaner(): void
    {
        $webResource = new WebResource('mock', 'mock', 'mock');
        $config = $this->createMock(ConfigManager::class);
        $config->method('getValidClasses')
            ->willReturn(['to-keep', 'top']);

        $internalLink = new ClassCleaner($config);
        $crawler = new Crawler(
            '<div class="to-keep       top no get-away">foobar</div>'
        );

        $internalLink->process($webResource, $crawler->filter('body'));
        $this->assertEquals(
            '<div class="to-keep top">foobar</div>',
            $crawler->filter('body')->html()
        );
    }

    public function testClassCleanerNested(): void
    {
        $webResource = new WebResource('mock', 'mock', 'mock');
        $config = $this->createMock(ConfigManager::class);
        $config->method('getValidClasses')
            ->willReturn(['to-keep', 'top']);

        $internalLink = new ClassCleaner($config);
        $crawler = new Crawler(
            '<div class="to-keep       top no get-away">foobar <div class="to-keep       top no get-away">foobar</div></div>'
        );

        $internalLink->process($webResource, $crawler->filter('body'));
        $this->assertEquals(
            '<div class="to-keep top">foobar <div class="to-keep top">foobar</div></div>',
            $crawler->filter('body')->html()
        );
    }
}
