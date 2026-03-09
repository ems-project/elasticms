<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Twig;

use EMS\CommonBundle\Tests\Integration\App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class CommonExtensionTest extends KernelTestCase
{
    private Environment $twig;

    #[\Override]
    protected function setUp(): void
    {
        self::bootKernel();
        $this->twig = static::getContainer()->get('twig');
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testFilterEmsLink(): void
    {
        $template = $this->twig->createTemplate(
            <<<TEMPLATE
                            {%- set emsLink = 'page:064efcc7751ee8b0915416a717e2db46d15c77eb'|ems_link -%}
                            {{- emsLink.contentType }} | {{ emsLink.ouuid -}}   
                TEMPLATE
        );

        $this->assertEquals('page | 064efcc7751ee8b0915416a717e2db46d15c77eb', $template->render());
    }
}
