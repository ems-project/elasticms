#!/usr/bin/env bash

NGINX_PUBLIC_DIR_WCMTECH_DEFAULT="${APP_SRC_DIR}/public"

NGINX_SERVER_NAME_WCMTECH_DEFAULT="localhost"
ALIAS_WCMTECH_DEFAULT=""

NGINX_X_FRAME_OPTIONS_WCMTECH_DEFAULT="SAMEORIGIN"
NGINX_X_CONTENT_TYPE_OPTIONS_WCMTECH_DEFAULT="nosniff"
NGINX_REFERRER_POLICY_WCMTECH_DEFAULT=""
NGINX_PERMISSIONS_POLICY_WCMTECH_DEFAULT=""
NGINX_CONTENT_SECURITY_POLICY_WCMTECH_DEFAULT=""
NGINX_STRICT_TRANSPORT_SECURITY_WCMTECH_DEFAULT=""
# "1" asked old browsers to run their XSS auditor, which could itself be abused to
# leak data and is gone from every current browser. "0" turns it off where it
# still exists, as OWASP recommends; set it empty to send no header at all.
NGINX_X_XSS_PROTECTION_WCMTECH_DEFAULT="0"

# nginx refused a body PHP accepts: 5m against post_max_size 32M and
# upload_max_filesize 10M, so a 6 MB form post was a 413 from nginx before PHP
# had a say. The limit now follows post_max_size, the PHP limit on a whole
# request body, including when an operator changes it (10-php.sh is sourced
# first). nginx reads the same k/m/g suffixes, and 0 means no limit in both.
NGINX_CLIENT_MAX_BODY_SIZE_WCMTECH_DEFAULT="${PHP_POST_MAX_SIZE:-${PHP_POST_MAX_SIZE_WCMTECH_DEFAULT}}"

NGINX_BUNDLES_LOCATION_EXPIRES_WCMTECH_DEFAULT="off"
NGINX_BUNDLES_LOCATION_ACCESS_LOG_WCMTECH_DEFAULT="off"
NGINX_BUNDLES_LOCATION_CACHE_CONTROL_WCMTECH_DEFAULT="public, max-age=31536000, immutable"

NGINX_STATIC_LOCATION_EXPIRES_WCMTECH_DEFAULT="off"
NGINX_STATIC_LOCATION_ACCESS_LOG_WCMTECH_DEFAULT="off"
NGINX_STATIC_LOCATION_CACHE_CONTROL_WCMTECH_DEFAULT="public, max-age=2592000"

NGINX_DENY_LOCATION_REGEXP_WCMTECH_DEFAULT="/\.(?!well-known).*"

true
