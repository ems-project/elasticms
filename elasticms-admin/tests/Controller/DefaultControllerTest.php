<?php

declare(strict_types=1);

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @env KERNEL_CLASS=App\Kernel
 * @env EMS_CACHE_PREFIX=test_cache_prefix
 * @env EMS_STORAGES=[{"type":"fs","path":"/tmp"}]
 */
class DefaultControllerTest extends WebTestCase
{
    public function testRedirect(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertEquals('/dashboard', $client->getResponse()->headers->get('location'));
    }

    //    public function testIndex()
    //    {
    //        $client = static::createClient();
    //        $crawler = $client->request('GET', '/login');
    //        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    //        $this->assertContains('Sign in to start your session', $crawler->filter('p.login-box-msg')->text());
    //    }
    //
    //    public function testLogin()
    //    {
    //        $client = static::createClient();
    //
    //        $crawler = $client->request('GET', '/login');
    //
    //        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    //        $this->assertContains('Sign in to start your session', $crawler->filter('p.login-box-msg')->text());
    //    }
    //
    //    public function testHealthCheck()
    //    {
    //        $client = static::createClient();
    //
    //        $crawler = $client->request('GET', '/health_check');
    //
    //        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    //        $this->assertContains('Status of the cluster', $crawler->filter('h1')->text());
    //    }
}
