<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Filter;

use App\CLI\Client\HttpClient\CacheManager;
use App\CLI\Client\WebToElasticms\Config\ConfigManager;
use App\CLI\Client\WebToElasticms\Config\WebResource;
use App\CLI\Client\WebToElasticms\Filter\Html\InternalLink;
use App\CLI\Client\WebToElasticms\Rapport\Rapport;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class InternaleLinkTest extends TestCase
{
    public function testInternalLink(): void
    {
        $webResource = new WebResource('mock', 'mock', 'mock');
        $config = $this->createMock(ConfigManager::class);
        $logger = $this->createMock(LoggerInterface::class);
        $config->method('getHosts')
            ->willReturn(['demo.com']);
        $config->method('findInternalLink')
            ->willReturn('ems://object:page:ouuid');

        $cacheManager = new CacheManager(\sys_get_temp_dir());
        $rapport = new Rapport($cacheManager, \sys_get_temp_dir());

        $internalLink = new InternalLink($config, $rapport, 'https://demo.com/a/b');

        $crawler = new Crawler(
            '<div style="padding: inherit;"><a href="https://demo.com/toto/link">Url</a></div>
<div style="padding: inherit;"><a href="//demo.com/toto/link">Url</a></div>
<div style="padding: inherit;"><a href="/toto/link">Absolute link</a></div>
<div style="padding: inherit;"><a href="../../toto/link">Absolute link</a></div>
<div style="padding: inherit;"><img src="../asset/images/test.png"></div>
<div style="padding: inherit;"><a href="https://www.google.com">Google</a></div>
<div style="padding: inherit;"><a href="//www.google.com">Google</a></div>'
        );

        $internalLink->process($webResource, $crawler->filter('body'));
        $this->assertEquals(
            '<div style="padding: inherit;"><a href="ems://object:page:ouuid">Url</a></div>
<div style="padding: inherit;"><a href="ems://object:page:ouuid">Url</a></div>
<div style="padding: inherit;"><a href="ems://object:page:ouuid">Absolute link</a></div>
<div style="padding: inherit;"><a href="ems://object:page:ouuid">Absolute link</a></div>
<div style="padding: inherit;"><img src="ems://object:page:ouuid"></div>
<div style="padding: inherit;"><a href="https://www.google.com">Google</a></div>
<div style="padding: inherit;"><a href="//www.google.com">Google</a></div>',
            $crawler->filter('body')->html()
        );
    }

    public function testLinkToClean(): void
    {
        $webResource = new WebResource('mock', 'mock', 'mock');
        $cacheManager = new CacheManager(\sys_get_temp_dir());
        $rapport = new Rapport($cacheManager, \sys_get_temp_dir());
        $config = $this->createMock(ConfigManager::class);
        $logger = $this->createMock(LoggerInterface::class);
        $config->method('getHosts')
            ->willReturn(['demo.com']);
        $config->method('findInternalLink')
            ->willReturn('ems://object:page:ouuid');
        $config->method('getLinkToClean')
            ->willReturn(["/^\/fr\/glossaire/"]);
        $crawler = new Crawler(
            '<div style="padding: inherit;"><a href="//demo.com/fr/glossaire?totot">Url</a></div>
<div style="padding: inherit;"><a href="../fr/glossaire">link</a></div>
<div style="padding: inherit;"><a href="/fr/glossaire">link</a></div>
<div style="padding: inherit;"><a href="/autre">link</a> toto <a href="/fr/glossaire">link</a> totot</div>'
        );
        $internalLink = new InternalLink($config, $rapport, 'https://demo.com/a/b');

        $internalLink->process($webResource, $crawler->filter('body'));
        $this->assertEquals('<div style="padding: inherit;">Url</div>
<div style="padding: inherit;">link</div>
<div style="padding: inherit;">link</div>
<div style="padding: inherit;"><a href="ems://object:page:ouuid">link</a> toto link totot</div>', $crawler->filter('body')->html());
    }
}
