#!/usr/bin/env bash

LOG_LEVEL_WCMTECH_DEFAULT="WARNING"
CLI_PHP_MEMORY_LIMIT_WCMTECH_DEFAULT="512M"

if [ ! -z "$AWS_S3_ENDPOINT_URL" ]; then
    export AWS_CLI_EXTRA_ARGS="--endpoint-url ${AWS_S3_ENDPOINT_URL}"
fi

true
