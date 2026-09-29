#!/usr/bin/env bash

# LOG_LEVEL is read by the application (monolog.yaml), so it is an application
# default, not an image setting: as a <NAME>_WCMTECH_DEFAULT, base-php unset it
# before php-fpm started, and unless an instance configuration set it again
# Symfony fell back to its own default, 100 (DEBUG).
export LOG_LEVEL="${LOG_LEVEL:-WARNING}"
CLI_PHP_MEMORY_LIMIT_WCMTECH_DEFAULT="512M"

if [ ! -z "$AWS_S3_ENDPOINT_URL" ]; then
    export AWS_CLI_EXTRA_ARGS="--endpoint-url ${AWS_S3_ENDPOINT_URL}"
fi

true
