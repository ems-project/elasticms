<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

class Methods
{
    public const GET = 'GET';
    public const HEAD = 'HEAD';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const DELETE = 'DELETE';
    public const CONNECT = 'CONNECT';
    public const OPTIONS = 'OPTIONS';
    public const TRACE = 'TRACE';
    public const PATCH = 'PATCH';

    public const WITH_PAYLOAD_BODY = [
        self::GET,
        self::POST,
        self::PUT,
        self::DELETE,
        self::CONNECT,
        self::OPTIONS,
        self::TRACE,
        self::PATCH,
    ];

    public const SAFE = [
        self::GET,
        self::HEAD,
        self::OPTIONS,
        self::TRACE,
    ];

    public const IDEMPOTENT = [
        self::GET,
        self::HEAD,
        self::PUT,
        self::DELETE,
        self::OPTIONS,
        self::TRACE,
    ];

    public const CACHEABLE = [
        self::GET,
        self::HEAD,
        self::POST,
    ];
}
