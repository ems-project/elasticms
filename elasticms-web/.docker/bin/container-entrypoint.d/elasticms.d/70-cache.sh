#!/usr/bin/env bash

log "INFO" "+ Running ElasticMS cache warming up for [ ${ELASTICMS_INSTANCE_NAME} ] WebSite Domain ..."

# Not fatal: the cache warms up on the first requests instead.
if ! ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} cache:warm --no-interaction --env=${APP_ENV}; then
    log "WARN" "! Something doesn't work with ElasticMS cache warming up !"
fi
