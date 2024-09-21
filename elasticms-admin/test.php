<?php

require __DIR__.'/../vendor/autoload.php';

$httpClient = new \Symfony\Component\HttpClient\CurlHttpClient([
    'base_uri' => 'https://localhost:8881',
    'http_version' => '2.0',

]);



