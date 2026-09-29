#!/usr/bin/env bash

PHP_CGI_FIX_PATHINFO_WCMTECH_DEFAULT="0"
PHP_POST_MAX_SIZE_WCMTECH_DEFAULT="32M"
PHP_UPLOAD_MAX_FILESIZE_WCMTECH_DEFAULT="10M"

# OPcache is shared by every pool of the container, so by every instance, each
# loading its own compiled container. Measured with two instances after warming
# both: the interned-strings buffer full (8 of 8 MB), past which each worker keeps
# its own copy of the strings, and the shared memory about half used (65 of
# 128 MB on the admin). Symfony recommends 16 MB of interned strings at least.
PHP_OPCACHE_INTERNED_STRINGS_BUFFER_WCMTECH_DEFAULT="32"
PHP_OPCACHE_MEMORY_CONSUMPTION_WCMTECH_DEFAULT="256"
# opcache.validate_timestamps stays on, on purpose: ElasticMS compiles templates
# edited in the admin into PHP files at runtime, and a stale compiled template is
# worse than a stat every two seconds.

true
