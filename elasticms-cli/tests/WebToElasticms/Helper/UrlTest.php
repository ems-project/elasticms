<?php

declare(strict_types=1);

namespace App\CLI\Tests\WebToElasticms\Helper;

use App\CLI\Client\WebToElasticms\Helper\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function testUrl(): void
    {
        $this->assertEquals('https://google.com/', (new Url('https://google.com'))->getUrl());
        $this->assertEquals('https://user:password@google.com/', (new Url('https://user:password@google.com'))->getUrl());
        $this->assertEquals('https://user:password@google.com/toto.txt', (new Url('/aa/../bb/vv/../../toto.txt', 'https://user:password@google.com'))->getUrl());
        $this->assertEquals('https://user:password@google.com/bb/toto.txt', (new Url('/aa/../bb/cc/vv/../../toto.txt', 'https://user:password@google.com'))->getUrl());
        $this->assertEquals('https://user:password@google.com/toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/../'))->getUrl());
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/'))->getUrl());
        $this->assertEquals('https://user:password@google.com/toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa'))->getUrl());
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/'))->getUrl());
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt#anchor', (new Url('./toto.txt#anchor', 'https://user:password@google.com/aaa/'))->getUrl(null, true));
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt?anchor=toto&foo=bar', (new Url('./toto.txt?anchor=toto&foo=bar', 'https://user:password@google.com/aaa/'))->getUrl());
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt#anchor?anchor=toto&foo=bar', (new Url('./toto.txt#anchor?anchor=toto&foo=bar', 'https://user:password@google.com/aaa/'))->getUrl(null, true));
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/#anchor?anchor=toto&foo=bar'))->getUrl());
        $this->assertEquals('https://www.socialsecurity.be/site_nl/general/cookies.htm', (new Url('../../../general/cookies.htm', 'https://www.socialsecurity.be/site_nl/civilservant/Infos/general/index.htm'))->getUrl());
    }

    public function testFilename(): void
    {
        $this->assertEquals('index.html', (new Url('https://google.com'))->getFilename());
        $this->assertEquals('index.html', (new Url('https://user:password@google.com'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('/aa/../bb/vv/../../toto.txt', 'https://user:password@google.com'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/../'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt#anchor', 'https://user:password@google.com/aaa/'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt?anchor=toto&foo=bar', 'https://user:password@google.com/aaa/'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt#anchor?anchor=toto&foo=bar', 'https://user:password@google.com/aaa/'))->getFilename());
        $this->assertEquals('toto.txt', (new Url('./toto.txt', 'https://user:password@google.com/aaa/#anchor?anchor=toto&foo=bar'))->getFilename());
    }

    public function testSerialize(): void
    {
        $this->assertEquals('{"url":"https://google.com/","referer":null}', (new Url('https://google.com'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/","referer":null}', (new Url('https://user:password@google.com'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/toto.txt","referer":"https://user:password@google.com/"}', (new Url('/aa/../bb/vv/../../toto.txt', 'https://user:password@google.com'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/toto.txt","referer":"https://user:password@google.com/"}', (new Url('./toto.txt', 'https://user:password@google.com/aaa/../'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/toto.txt","referer":"https://user:password@google.com/aaa"}', (new Url('./toto.txt', 'https://user:password@google.com/aaa'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/aaa/toto.txt","referer":"https://user:password@google.com/aaa/"}', (new Url('./toto.txt', 'https://user:password@google.com/aaa/'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/aaa/toto.txt","referer":"https://user:password@google.com/aaa/"}', (new Url('./toto.txt#anchor', 'https://user:password@google.com/aaa/'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/aaa/toto.txt?anchor=toto&foo=bar","referer":"https://user:password@google.com/aaa/"}', (new Url('./toto.txt?anchor=toto&foo=bar', 'https://user:password@google.com/aaa/'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/aaa/toto.txt","referer":"https://user:password@google.com/aaa/"}', (new Url('./toto.txt#anchor?anchor=toto&foo=bar', 'https://user:password@google.com/aaa/'))->serialize());
        $this->assertEquals('{"url":"https://user:password@google.com/aaa/toto.txt","referer":"https://user:password@google.com/aaa/#anchor?anchor=toto&foo=bar"}', (new Url('./toto.txt', 'https://user:password@google.com/aaa/#anchor?anchor=toto&foo=bar'))->serialize());
    }

    public function testDeserialize(): void
    {
        $url = Url::deserialize('{"url":"https://google.com/","referer":null}');
        $this->assertEquals('/', $url->getPath());
        $this->assertEquals(null, $url->getReferer());
        $this->assertEquals(null, $url->getPort());
        $url = Url::deserialize('{"url":"https://user:password@google.com/aaa/toto.txt","referer":"https://user:password@google.com/aaa/#anchor?anchor=toto&foo=bar"}');
        $this->assertEquals('https://user:password@google.com/aaa/toto.txt', $url->getUrl());
        $this->assertEquals('https://user:password@google.com/aaa/#anchor?anchor=toto&foo=bar', $url->getReferer());
    }
}
