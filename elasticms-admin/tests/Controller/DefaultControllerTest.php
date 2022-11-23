<?php

declare(strict_types=1);

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
        $this->assertContains('Sign in to start your session', $crawler->filter('p.login-box-msg')->text());
    }

    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Sign in to start your session', $crawler->filter('p.login-box-msg')->text());
    }

    public function testHealthCheck()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/health_check');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Status of the cluster', $crawler->filter('h1')->text());
    }
}
