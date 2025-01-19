<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional;

use EMS\SubmissionBundle\Tests\Functional\App\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class AbstractFunctionalTestCase extends TestCase
{
    /** @var ContainerInterface */
    protected $container;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = new Kernel('test', true);
        $kernel->boot();

        $this->container = $kernel->getContainer();
    }
}
