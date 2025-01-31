<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Integration;

use EMS\CommonBundle\Tests\Integration\App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class BootTest extends KernelTestCase
{
    public function testKernelIsBooted()
    {
        self::bootKernel();
        $this->assertTrue(self::$booted);
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
