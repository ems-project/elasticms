#!/usr/bin/env bash

export APP_BIN_DIR="/opt/sbin"
export APP_SRC_DIR="/app/src/elasticms"
export APP_TMP_DIR="${TMPDIR}"
export APP_CONFIG_DIR="${APP_TMP_DIR}/elasticms.d"
export APP_CONFIG_JSON_DIR="${APP_TMP_DIR}/json.d"
export APP_LOG_DIR="/app/var/log/elasticms"
export APP_CACHE_DIR="/app/var/cache/elasticms"

true
