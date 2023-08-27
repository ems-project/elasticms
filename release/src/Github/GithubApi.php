<?php

declare(strict_types=1);

namespace EMS\Release\Github;

use Github\Api\GraphQL;
use Github\AuthMethod;
use Github\Client;

class GithubApi
{
    public Client $api;

    public function __construct(private readonly string $githubToken)
    {
        $this->api = new Client(null, 'v4');
        $this->api->authenticate($this->githubToken, AuthMethod::JWT);
    }

    public function graphql(): GraphQL
    {
        return $this->api->graphql();
    }
}
