#!/usr/bin/env bash

log "INFO" "+ Running Elasticms cache warming up for [ ${ELASTICMS_INSTANCE_NAME} ] CMS Domain ..."

${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} cache:warm --no-interaction --env=${APP_ENV}

if [ $? -ne 0 ]; then
    log "ERROR" "! Something doesn't work with Elasticms cache warming up !"
fi