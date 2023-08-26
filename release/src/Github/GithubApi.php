<?php

declare(strict_types=1);

namespace EMS\Release\Github;

use Github\AuthMethod;
use Github\Client;

class GithubApi
{
    public Client $api;

    public function __construct(private readonly string $githubToken)
    {
        $client = new Client();
        $client->authenticate($this->githubToken, AuthMethod::JWT);

        $this->api = $client;
    }
}
