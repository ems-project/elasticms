<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\DependencyInjection;

use EMS\SubmissionBundle\DependencyInjection\EMSSubmissionExtension;
use EMS\SubmissionBundle\EMSSubmissionBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EMSSubmissionExtensionTest extends TestCase
{
    public function testExtension(): void
    {
        $container = $this->getRawContainer();
        $container->setParameter('kernel.bundles', []);
        $container->loadFromExtension('ems_submission', [
            'default_timeout' => 20,
            'connections' => [
                [
                    'connection' => 'service-now-instance-a',
                    'user' => 'userA',
                    'password' => 'secret',
                ],
                [
                    'connection' => 'service-now-instance-b',
                    'user' => 'userB',
                    'password' => 'secret2',
                ],
            ],
        ]);
        $container->compile();

        $this->assertEquals(20, $container->getParameter('emss.default_timeout'));
        $this->assertCount(2, $container->getParameter('emss.connections'));
    }

    protected function getRawContainer()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);

        $submissionExtension = new EMSSubmissionExtension();
        $container->registerExtension($submissionExtension);

        $bundle = new EMSSubmissionBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        return $container;
    }
}
