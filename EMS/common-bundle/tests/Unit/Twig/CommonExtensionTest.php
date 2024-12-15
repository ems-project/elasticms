<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Twig;

use EMS\CommonBundle\Twig\CommonExtension;
use Monolog\Test\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class CommonExtensionTest extends TestCase
{
    private Environment $twig;

    public function setUp(): void
    {
        $twig = new Environment(
            new ArrayLoader([]),
            ['debug' => true, 'cache' => false, 'autoescape' => 'html', 'optimizations' => 0]
        );
        $twig->addExtension(new CommonExtension());
        $this->twig = $twig;
    }

    public function testFilterEmsLink(): void
    {
        $template = $this->twig->createTemplate(<<<TEMPLATE
            {%- set emsLink = 'page:064efcc7751ee8b0915416a717e2db46d15c77eb'|ems_link -%}
            {{- emsLink.contentType }} | {{ emsLink.ouuid -}}   
TEMPLATE
        );

        $this->assertEquals('page | 064efcc7751ee8b0915416a717e2db46d15c77eb', $template->render());
    }
}
