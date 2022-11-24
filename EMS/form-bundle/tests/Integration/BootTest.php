<?php

declare(strict_types=1);

namespace EMS\FormBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @env KERNEL_CLASS=EMS\FormBundle\Tests\Integration\App\Kernel
 */
final class BootTest extends KernelTestCase
{
    public function testKernelIsBooted()
    {
        self::bootKernel();
        $this->assertTrue(self::$booted);
    }
}
