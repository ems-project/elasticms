<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Connection;

use EMS\SubmissionBundle\Connection\Transformer;
use EMS\SubmissionBundle\Twig\ConnectionExtension;
use EMS\SubmissionBundle\Twig\ConnectionRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\RuntimeLoader\ContainerRuntimeLoader;

final class ConnectionExtensionTest extends TestCase
{
    public function testEMSConnection(): void
    {
        $transformer = new Transformer([[
            'connection' => 'service-now-instance-a',
            'user' => 'david',
            'password' => 'its_a_secret',
        ]]);
        $this->assertEquals('', $transformer->transform([]));

        $def = new Definition(ConnectionRuntime::class, [$transformer]);

        $container = new ContainerBuilder();
        $container->setDefinition(ConnectionRuntime::class, $def);

        $twig = new Environment(
            new ArrayLoader([
                'testGetUser.html.twig' => "{{ 'service-now-instance-a%.%user'|emss_connection }}",
                'testGetPassword.html.twig' => "{{ 'service-now-instance-a%.%password'|emss_connection }}",
                'testUnknownConnection.html.twig' => "{{ 'service-now-unknown%.%user'|emss_connection }}",
                'testMethodNotExists.html.twig' => "{{ 'service-now-instance-a%.%methodTest'|emss_connection }}",
                'testEmpty.html.twig' => "{{ ''|emss_connection }}",
                'testOnlySeparator.html.twig' => "{{ '%.%'|emss_connection }}",
            ]),
            ['debug' => true, 'cache' => false, 'autoescape' => 'html', 'optimizations' => 0]
        );
        $twig->addExtension(new ConnectionExtension());
        $twig->addRuntimeLoader(new ContainerRuntimeLoader($container));

        $this->assertEquals('david', $twig->render('testGetUser.html.twig', []));
        $this->assertEquals('its_a_secret', $twig->render('testGetPassword.html.twig', []));
        $this->assertEquals('service-now-unknown', $twig->render('testUnknownConnection.html.twig', []));
        $this->assertEquals('methodTest', $twig->render('testMethodNotExists.html.twig', []));
        $this->assertEquals('', $twig->render('testEmpty.html.twig', []));
        $this->assertEquals('', $twig->render('testOnlySeparator.html.twig', []));
    }
}
