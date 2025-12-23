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
    final public const string X_CACHE_TAGS = 'X-Cache-Tags';
    final public const string WWW_AUTHENTICATE = 'WWW-Authenticate';
    final public const string SET_COOKIE = 'set-cookie';
    final public const string COOKIE = 'cookie';
    final public const string IF_NONE_MATCH = 'if-none-match';
    final public const string LINK = 'link';
    final public const string X_WEBHOOK_EVENT = 'X-Webhook-Event';
    final public const string X_WEBHOOK_SIGNATURE = 'X-Webhook-Signature';
    final public const string X_WEBHOOK_SUBSCRIPTION_ID = 'X-Webhook-Subscription-Id';
}
