<?php

declare(strict_types=1);

namespace EMS\Helpers\Html;

class Headers
{
    final public const string AUTHORIZATION = 'Authorization';
    final public const string CONTENT_DISPOSITION = 'Content-Disposition';
    final public const string CONTENT_LENGTH = 'Content-Length';
    final public const string CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
    final public const string CONTENT_SECURITY_POLICY_REPORT_ONLY = 'Content-Security-Policy-Report-Only';
    final public const string CONTENT_TYPE = 'Content-Type';
    final public const string COOKIE = 'Cookie';
    final public const string IF_NONE_MATCH = 'If-None-Match';
    final public const string LINK = 'Link';
    final public const string PERMISSIONS_POLICY = 'Permissions-Policy';
    final public const string REFERRER_POLICY = 'Referrer-Policy';
    final public const string SET_COOKIE = 'set-cookie';
    final public const string X_CACHE_TAGS = 'X-Cache-Tags';
    final public const string X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    final public const string X_FILE_SIZE = 'X-File-Size';
    final public const string X_HASHCASH = 'X-Hashcash';
    final public const string X_ROBOTS_TAG = 'X-Robots-Tag';
    final public const string X_WEBHOOK_EVENT = 'X-Webhook-Event';
    final public const string X_WEBHOOK_SIGNATURE = 'X-Webhook-Signature';
    final public const string X_WEBHOOK_SUBSCRIPTION_ID = 'X-Webhook-Subscription-Id';
    final public const string WWW_AUTHENTICATE = 'WWW-Authenticate';

    /** Values **/
    final public const string X_CONTENT_TYPE_OPTIONS_NOSNIFF = 'nosniff';
    final public const string X_ROBOTS_TAG_NOINDEX = 'noindex';
    final public const string REFERRER_POLICY_STRICT_ORIGIN_WHEN_CROSS_ORIGIN = 'strict-origin-when-cross-origin';

    public static function normalizeName(string $name): string
    {
        if (!self::validateName($name)) {
            throw new \InvalidArgumentException('Invalid header name');
        }

        return \ucwords(\strtolower($name), '-');
    }

    public static function validateValue(string $value): bool
    {
        return 1 === \preg_match('~\A[\x09\x20-\x7E\x80-\xFF]*\z~', $value);
    }

    public static function validateName(string $name): bool
    {
        return 1 === \preg_match("~\\A[!#$%&'*+\\-.^_`|\\~0-9A-Za-z]+\\z~", $name);
    }
}
