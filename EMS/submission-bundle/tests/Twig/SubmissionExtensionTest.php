<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Twig;

use EMS\SubmissionBundle\Tests\Functional\App\Kernel;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class SubmissionExtensionTest extends KernelTestCase
{
    private Environment $twig;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->twig = static::getContainer()->get(Environment::class);
    }

    public static function templates(): array
    {
        return [
            'testGetUser' => ["{{ 'service-now-instance-a%.%user'|emss_connection }}", 'userA'],
            'testGetPassword' => ["{{ 'service-now-instance-a%.%password'|emss_connection }}", 'passB'],
            'testUnknownConnection' => ["{{ 'service-now-unknown%.%user'|emss_connection }}",  'service-now-unknown'],
            'testMethodNotExists' => ["{{ 'service-now-instance-a%.%methodTest'|emss_connection }}", 'methodTest'],
            'testEmpty' => ["{{ ''|emss_connection }}", ''],
            'testOnlySeparator' => ["{{ '%.%'|emss_connection }}", ''],
        ];
    }

    #[DataProvider(methodName: 'templates')]
    public function testEMSConnection(string $twig, string $expected): void
    {
        $template = $this->twig->createTemplate($twig);

        $this->assertEquals($expected, $this->twig->render($template));
    }
}
