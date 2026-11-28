<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Command\Website;

use EMS\CommonBundle\Command\Website\RewriteUrlsCommand;
use EMS\CommonBundle\Commands;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class RewriteUrlsCommandTest extends TestCase
{
    private Filesystem $filesystem;
    private string $root;
    private string $source;
    private string $target;

    #[\Override]
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->root = Path::join(\sys_get_temp_dir(), 'ems_rewrite_urls_'.\uniqid());
        $this->source = Path::join($this->root, 'source');
        $this->target = Path::join($this->root, 'target');
        $this->filesystem->mkdir([$this->source, $this->target]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->filesystem->remove($this->root);
    }

    public function testDefaultCopyPreservesStructure(): void
    {
        $this->write('.htaccess', 'Options -Indexes');
        $this->write('img/a/logo.png', "\x89PNG\x00\x01");
        $this->write('data.json', '{"url":"/img/a/logo.png"}');

        $tester = $this->runCommand();

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertSame('Options -Indexes', $this->read('.htaccess'));
        $this->assertSame("\x89PNG\x00\x01", $this->read('img/a/logo.png'));
        $this->assertSame('{"url":"/img/a/logo.png"}', $this->read('data.json'));
    }

    public function testHtmlLinksAreMadeRelative(): void
    {
        $this->write('css/s.css', '');
        $this->write('my file.png', '');
        $this->write('a/b/index.html', '<html><body><a id="css" href="/css/s.css">c</a><a id="root" href="/">r</a><a id="self" href="/a/b/">s</a></body></html>');
        $this->write('index.html', <<<'HTML'
            <!DOCTYPE html><html><head><link href="/css/s.css" rel="stylesheet"></head><body>
            <a id="dir" href="/a/b/">dir</a>
            <a id="abs" href="https://d.tld/a/b/index.html?x=1#f">abs</a>
            <a id="proto" href="//d.tld/css/s.css">proto</a>
            <a id="root" href="/">root</a>
            <a id="missing" href="/missing.html">missing</a>
            <a id="external" href="//other.com/css/s.css">external</a>
            <a id="evil" href="https://d.tld.evil/css/s.css">evil</a>
            <a id="dots" href="/a/../css/s.css">dots</a>
            <a id="escape" href="/../css/s.css">escape</a>
            <a id="mail" href="mailto:a@b.c">mail</a>
            <img id="img" src="/my%20file.png">
            </body></html>
            HTML);

        $this->runCommand(['/', 'https://d.tld', '//d.tld']);

        $html = $this->read('index.html');
        $this->assertStringContainsString('href="css/s.css" rel="stylesheet"', $html);
        $this->assertStringContainsString('id="dir" href="a/b/"', $html);
        $this->assertStringContainsString('id="abs" href="a/b/index.html?x=1#f"', $html);
        $this->assertStringContainsString('id="proto" href="css/s.css"', $html);
        $this->assertStringContainsString('id="root" href="./"', $html);
        $this->assertStringContainsString('id="missing" href="/missing.html"', $html);
        $this->assertStringContainsString('id="external" href="//other.com/css/s.css"', $html);
        $this->assertStringContainsString('id="evil" href="https://d.tld.evil/css/s.css"', $html);
        $this->assertStringContainsString('id="dots" href="css/s.css"', $html);
        $this->assertStringContainsString('id="escape" href="/../css/s.css"', $html);
        $this->assertStringContainsString('id="mail" href="mailto:a@b.c"', $html);
        $this->assertStringContainsString('id="img" src="my%20file.png"', $html);

        $nested = $this->read('a/b/index.html');
        $this->assertStringContainsString('id="css" href="../../css/s.css"', $nested);
        $this->assertStringContainsString('id="root" href="../../"', $nested);
        $this->assertStringContainsString('id="self" href="./"', $nested);
    }

    public function testHtmExtensionAndDataAttributes(): void
    {
        $this->write('css/s.css', '');
        $this->write('a/page.htm', '<html><body><div data-src="/css/s.css" data-x="/nope.css" data-json="{&quot;a&quot;:1}" class="/css/s.css"></div></body></html>');

        $this->runCommand(['/']);

        $html = $this->read('a/page.htm');
        $this->assertStringContainsString('data-src="../css/s.css"', $html);
        $this->assertStringContainsString('data-x="/nope.css"', $html);
        $this->assertStringContainsString('data-json="{&quot;a&quot;:1}"', $html);
        $this->assertStringContainsString('class="/css/s.css"', $html);
    }

    public function testQueryStringIsIgnoredToFindTheFileButKept(): void
    {
        $this->write('wp-includes/js/index.min.js', '');
        $this->write('page/index.html', '<html><body><script type="module" src="https://d.tld/wp-includes/js/index.min.js?ver=55aebb6e0a16726baffb"></script><script src="/wp-includes/js/missing.js?ver=1"></script></body></html>');

        $this->runCommand(['/', 'https://d.tld']);

        $html = $this->read('page/index.html');
        $this->assertStringContainsString('src="../wp-includes/js/index.min.js?ver=55aebb6e0a16726baffb"', $html);
        $this->assertStringContainsString('src="/wp-includes/js/missing.js?ver=1"', $html);
    }

    public function testImportMapInHtml(): void
    {
        $this->write('wp/js/interactivity/index.min.js', '');
        $this->write('wp/js/other.js', '');
        $this->write('page/index.html', '<html><head><script type="importmap" id="wp-importmap">{"imports":{"@wp\/interactivity":"https:\/\/d.tld\/wp\/js\/interactivity\/index.min.js?ver=1","other":"/wp/js/other.js","ext":"https:\/\/cdn.tld\/x.js"}}</script></head><body></body></html>');
        $this->write('index.html', '<html><head><script type="importmap">{"imports":{"other":"/wp/js/other.js"}}</script></head></html>');

        $this->runCommand(['/', 'https://d.tld']);

        $html = $this->read('page/index.html');
        $this->assertStringContainsString('"@wp\/interactivity":"../wp/js/interactivity/index.min.js?ver=1"', $html);
        $this->assertStringContainsString('"other":"../wp/js/other.js"', $html);
        $this->assertStringContainsString('"ext":"https:\/\/cdn.tld\/x.js"', $html);
        $this->assertStringContainsString('"other":"./wp/js/other.js"', $this->read('index.html'));
    }

    public function testStyleTagsAndAttributes(): void
    {
        $this->write('wp/themes/t/assets/fonts/p.woff2', '');
        $this->write('img/bg.png', '');
        $this->write('page/index.html', <<<'HTML'
            <html><head><style class="wp-fonts-local">
            @font-face{font-family:Poppins;src:url('https://d.tld/wp/themes/t/../t/assets/fonts/p.woff2') format('woff2');}
            @font-face{font-family:Other;src:url('https://d.tld/wp/missing.woff2') format('woff2');}
            </style></head><body><div style="background:url(/img/bg.png) no-repeat"></div></body></html>
            HTML);

        $this->runCommand(['/', 'https://d.tld']);

        $html = $this->read('page/index.html');
        $this->assertStringContainsString("src:url('../wp/themes/t/assets/fonts/p.woff2') format('woff2');", $html);
        $this->assertStringContainsString("src:url('https://d.tld/wp/missing.woff2') format('woff2');", $html);
        $this->assertStringContainsString('style="background:url(../img/bg.png) no-repeat"', $html);
    }

    public function testSrcsetWithMultipleUrls(): void
    {
        $this->write('up/b.jpg', '');
        $this->write('up/b-300x200.jpg', '');
        $this->write('up/w_1,h_2/c.jpg', '');
        $this->write('page/index.html', <<<'HTML'
            <html><body>
            <img id="wp" srcset="https://d.tld/up/b.jpg 2560w, https://d.tld/up/b-300x200.jpg 300w, https://d.tld/up/missing.jpg 18w, //cdn.tld/x.jpg 2x" src="/up/b.jpg">
            <img id="nospace" srcset="/up/b.jpg 1x,/up/b-300x200.jpg 2x">
            <img id="comma" srcset="/up/w_1,h_2/c.jpg 1x, /up/b.jpg, /up/b-300x200.jpg">
            <img id="data" data-srcset="/up/b.jpg 1x, /up/b-300x200.jpg 2x">
            <link id="preload" rel="preload" as="image" imagesrcset="/up/b.jpg 1x, /up/b-300x200.jpg 2x">
            </body></html>
            HTML);

        $this->runCommand(['/', 'https://d.tld']);

        $html = $this->read('page/index.html');
        $this->assertStringContainsString('srcset="../up/b.jpg 2560w, ../up/b-300x200.jpg 300w, https://d.tld/up/missing.jpg 18w, //cdn.tld/x.jpg 2x" src="../up/b.jpg"', $html);
        $this->assertStringContainsString('id="nospace" srcset="../up/b.jpg 1x,../up/b-300x200.jpg 2x"', $html);
        $this->assertStringContainsString('id="comma" srcset="../up/w_1,h_2/c.jpg 1x, ../up/b.jpg, ../up/b-300x200.jpg"', $html);
        $this->assertStringContainsString('data-srcset="../up/b.jpg 1x, ../up/b-300x200.jpg 2x"', $html);
        $this->assertStringContainsString('imagesrcset="../up/b.jpg 1x, ../up/b-300x200.jpg 2x"', $html);
    }

    public function testOnlyGivenBaseUrlsAreApplied(): void
    {
        $this->write('css/s.css', '');
        $this->write('index.html', '<html><body><a href="https://d.tld/css/s.css">a</a><a href="/css/s.css">b</a></body></html>');

        $this->runCommand(['https://d.tld']);

        $html = $this->read('index.html');
        $this->assertStringContainsString('href="css/s.css">a', $html);
        $this->assertStringContainsString('href="/css/s.css">b', $html);
    }

    public function testIgnoreSelector(): void
    {
        $this->write('en/index.html', '');
        $this->write('fr/index.html', '');
        $this->write('index.html', '<html><body><ul class="language_switcher"><li class="__current"><a href="/en/">en</a></li><li><a href="/fr/">fr</a></li></ul><a href="/en/">x</a></body></html>');

        $this->tester()->execute([
            'source' => $this->source,
            'target' => $this->target,
            '--ignore-selector' => ['.language_switcher li:not(.__current) a'],
        ]);

        $html = $this->read('index.html');
        $this->assertStringContainsString('href="/fr/">fr', $html);
        $this->assertStringContainsString('href="en/">en', $html);
        $this->assertStringContainsString('href="en/">x', $html);
    }

    public function testPathMap(): void
    {
        $this->write('archives/2024/accueil/index.html', '');
        $this->write('archives/2024/index.html', '<html><body><a href="https://d.tld/archives/2024/nl/">a</a><a href="https://d.tld/archives/2024/nl/accueil/">b</a></body></html>');

        $this->tester()->execute([
            'source' => $this->source,
            'target' => $this->target,
            '--base-urls' => ['https://d.tld'],
            '--path-map' => ['/archives/2024/nl=/archives/2024'],
        ]);

        $html = $this->read('archives/2024/index.html');
        $this->assertStringContainsString('href="./">a', $html);
        $this->assertStringContainsString('href="accueil/">b', $html);
    }

    public function testNotRewrittenUrlsAreListedOnce(): void
    {
        $this->write('index.html', '<html><body><a href="/missing/">a</a><a href="/missing/">b</a><a href="https://other.tld/x">c</a></body></html>');

        $tester = $this->runCommand(['/']);

        $display = $tester->getDisplay();
        $this->assertSame(1, \substr_count($display, '/missing/'));
        $this->assertStringNotContainsString('other.tld', $display);
    }

    public function testCssUrlsAndImports(): void
    {
        $this->write('img/a.png', '');
        $this->write('img/b.svg', '');
        $this->write('css/base.css', '');
        $this->write('css/sub/m.css', <<<'CSS'
            @import "/css/base.css";
            @import url('https://d.tld/css/base.css');
            a{background:url(/img/a.png)}
            b{background:url( "//d.tld/img/b.svg" ) no-repeat}
            c{background:url(/nope.png)}
            d{background:url(data:image/png;base64,AAA=)}
            e{background:url(img/rel.png)}
            CSS);

        $this->runCommand(['/', 'https://d.tld', '//d.tld']);

        $css = $this->read('css/sub/m.css');
        $this->assertStringContainsString('@import "../base.css";', $css);
        $this->assertStringContainsString("@import url('../base.css');", $css);
        $this->assertStringContainsString('url(../../img/a.png)', $css);
        $this->assertStringContainsString('url( "../../img/b.svg" )', $css);
        $this->assertStringContainsString('url(/nope.png)', $css);
        $this->assertStringContainsString('url(data:image/png;base64,AAA=)', $css);
        $this->assertStringContainsString('url(img/rel.png)', $css);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function jsExtensions(): iterable
    {
        yield 'js' => ['js'];
        yield 'mjs' => ['mjs'];
        yield 'cjs' => ['cjs'];
    }

    #[DataProvider('jsExtensions')]
    public function testJsModuleSpecifiers(string $extension): void
    {
        $this->write('js/util.js', '');
        $this->write('js/lib/x.mjs', '');
        $this->write("js/lib/m.$extension", <<<'JS'
            import a from "/js/util.js";
            import {b} from '/js/lib/x.mjs';
            import "/js/util.js";
            export * from "https://d.tld/js/lib/x.mjs";
            const c = await import("/js/util.js");
            import d from "/nope.js"; import e from "lodash"; import f from "./x.mjs";
            fetch("/js/util.js");
            JS);

        $this->runCommand(['/', 'https://d.tld']);

        $js = $this->read("js/lib/m.$extension");
        $this->assertStringContainsString('import a from "../util.js";', $js);
        $this->assertStringContainsString("import {b} from './x.mjs';", $js);
        $this->assertStringContainsString('import "../util.js";', $js);
        $this->assertStringContainsString('export * from "./x.mjs";', $js);
        $this->assertStringContainsString('await import("../util.js");', $js);
        $this->assertStringContainsString('import d from "/nope.js";', $js);
        $this->assertStringContainsString('import e from "lodash";', $js);
        $this->assertStringContainsString('import f from "./x.mjs";', $js);
        $this->assertStringContainsString('fetch("/js/util.js");', $js);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function jsonExtensions(): iterable
    {
        yield 'json' => ['json'];
        yield 'webmanifest' => ['webmanifest'];
    }

    #[DataProvider('jsonExtensions')]
    public function testJsonStringValues(string $extension): void
    {
        $this->write('img/icon.png', '');
        $this->write('app/index.html', '');
        $this->write("app/site.$extension", <<<'JSON'
            {
              "start_url": "/app/",
              "icons": [{"src": "https:\/\/d.tld\/img\/icon.png", "sizes": "192x192"}],
              "missing": "/nope.png",
              "name": "é /img/icon.png",
              "empty": {},
              "/img/icon.png": "key",
              "count": 3
            }
            JSON);

        $this->runCommand(['/', 'https://d.tld']);

        $expected = <<<'JSON'
            {
              "start_url": "./",
              "icons": [{"src": "../img/icon.png", "sizes": "192x192"}],
              "missing": "/nope.png",
              "name": "é /img/icon.png",
              "empty": {},
              "/img/icon.png": "key",
              "count": 3
            }
            JSON;
        $this->assertSame($expected, $this->read("app/site.$extension"));
    }

    public function testNonEmptyTargetIsKeptWhenNotConfirmed(): void
    {
        $this->write('index.html', '<html></html>');
        $this->filesystem->dumpFile(Path::join($this->target, 'old.txt'), 'old');

        $tester = $this->runCommand(['/'], [], ['interactive' => false]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertFileExists(Path::join($this->target, 'old.txt'));
        $this->assertFileDoesNotExist(Path::join($this->target, 'index.html'));
    }

    public function testNonEmptyTargetIsEmptiedWhenConfirmed(): void
    {
        $this->write('index.html', '<html></html>');
        $this->filesystem->dumpFile(Path::join($this->target, 'old.txt'), 'old');
        $this->filesystem->dumpFile(Path::join($this->target, '.hidden/old'), 'old');

        $tester = $this->runCommand(['/'], ['yes']);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileDoesNotExist(Path::join($this->target, 'old.txt'));
        $this->assertDirectoryDoesNotExist(Path::join($this->target, '.hidden'));
        $this->assertFileExists(Path::join($this->target, 'index.html'));
    }

    public function testMissingTargetIsCreatedRecursivelyWhenConfirmed(): void
    {
        $this->write('a/index.html', '<html></html>');
        $target = Path::join($this->root, 'deep/er/target');

        $tester = $this->runCommand(['/'], ['yes'], [], $target);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertFileExists(Path::join($target, 'a/index.html'));
    }

    public function testMissingTargetIsNotCreatedWhenRefused(): void
    {
        $target = Path::join($this->root, 'deep/er/target');

        $tester = $this->runCommand(['/'], ['no'], [], $target);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertDirectoryDoesNotExist($target);
    }

    public function testTargetIsAFile(): void
    {
        $target = Path::join($this->root, 'file');
        $this->filesystem->dumpFile($target, 'x');

        $tester = $this->runCommand(['/'], [], [], $target);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('[ERROR]', $tester->getDisplay());
    }

    public function testSourceIsNotADirectory(): void
    {
        $tester = $this->tester();
        $tester->execute(['source' => Path::join($this->root, 'nope'), 'target' => $this->target]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('[ERROR]', $tester->getDisplay());
    }

    private function tester(): CommandTester
    {
        $application = new Application();
        $application->addCommand(new RewriteUrlsCommand());

        return new CommandTester($application->find(Commands::WEBSITE_REWRITE_URLS));
    }

    /**
     * @param string[]             $baseUrls
     * @param list<string>         $inputs
     * @param array<string, mixed> $options
     */
    private function runCommand(array $baseUrls = [], array $inputs = [], array $options = [], ?string $target = null): CommandTester
    {
        $tester = $this->tester();
        $tester->setInputs($inputs);
        $tester->execute([
            'source' => $this->source,
            'target' => $target ?? $this->target,
            '--base-urls' => $baseUrls,
        ], $options);

        return $tester;
    }

    private function write(string $path, string $content): void
    {
        $this->filesystem->dumpFile(Path::join($this->source, $path), $content);
    }

    private function read(string $path): string
    {
        return \file_get_contents(Path::join($this->target, $path)) ?: '';
    }
}
