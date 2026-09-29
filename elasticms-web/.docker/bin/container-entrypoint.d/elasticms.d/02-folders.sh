#!/usr/bin/env bash

log "INFO" "| Create required folders"

# The instance's basic-auth password file: written by 41-basicauth.sh, read by
# the vhost that 40-nginx.sh renders.
export ELASTICMS_BASIC_AUTH_FILE="/opt/etc/nginx/htpasswd/${ELASTICMS_INSTANCE_NAME}"

OUTDIR="${APP_CONFIG_DIR} ${APP_CONFIG_JSON_DIR} ${APP_LOG_DIR} ${APP_CACHE_DIR}"

for dir in $OUTDIR; do
    mkdir -p "$dir"
done
