<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

class Headers
{
    final public const AUTHORIZATION = 'Authorization';
    final public const CONTENT_DISPOSITION = 'Content-Disposition';
    final public const CONTENT_TYPE = 'Content-Type';
    final public const X_ROBOTS_TAG = 'X-Robots-Tag';
    final public const X_ROBOTS_TAG_NOINDEX = 'noindex';
    final public const X_HASHCASH = 'x-hashcash';
    public const WWW_AUTHENTICATE = 'WWW-Authenticate';
    public const SET_COOKIE = 'set-cookie';
    public const COOKIE = 'cookie';
    public const IF_NONE_MATCH = 'if-none-match';
}
