<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

class Headers
{
    final public const string AUTHORIZATION = 'Authorization';
    final public const string CONTENT_DISPOSITION = 'Content-Disposition';
    final public const string CONTENT_TYPE = 'Content-Type';
    final public const string X_ROBOTS_TAG = 'X-Robots-Tag';
    final public const string X_ROBOTS_TAG_NOINDEX = 'noindex';
    final public const string X_HASHCASH = 'x-hashcash';
    public const WWW_AUTHENTICATE = 'WWW-Authenticate';
    public const SET_COOKIE = 'set-cookie';
    public const COOKIE = 'cookie';
    public const IF_NONE_MATCH = 'if-none-match';
}
