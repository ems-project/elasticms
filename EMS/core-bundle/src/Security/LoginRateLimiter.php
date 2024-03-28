<?php

namespace EMS\CoreBundle\Security;

use Symfony\Component\HttpFoundation\RateLimiter\RequestRateLimiterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class LoginRateLimiter implements RequestRateLimiterInterface
{
    private LimiterInterface $rateLimiter;

    public function __construct(RateLimiterFactory $rateLimiterFactory)
    {
      $this->rateLimiter = $rateLimiterFactory->create('login_attempts');
    }
    public function consume(Request $request): RateLimit
    {
      return $this->rateLimiter->consume($request->getClientIp());
    }
    public function reset(Request $request): void
    {
      $this->rateLimiter->reset();
    }
}
