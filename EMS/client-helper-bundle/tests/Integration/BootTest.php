<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @env KERNEL_CLASS=EMS\ClientHelperBundle\Tests\Integration\App\Kernel
 */
final class BootTest extends KernelTestCase
{
    public function testKernelIsBooted()
    {
        self::bootKernel();
        $this->assertTrue(self::$booted);
    }
}
