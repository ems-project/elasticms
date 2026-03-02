#!/usr/bin/env bash

log "INFO" "| Create required folders"

OUTDIR="${APP_BIN_DIR}/ems-jobs ${APP_CONFIG_DIR} ${APP_CONFIG_JSON_DIR} ${APP_LOG_DIR} ${APP_CACHE_DIR}"

for dir in $OUTDIR; do
    mkdir -p "$dir"
done

true
