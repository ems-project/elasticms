#!/usr/bin/env bash

log "INFO" "| Create ElasticMS WebSite Shell script in ${APP_BIN_DIR}"

gomplate -f /opt/config/sbin/instance.sh.gtpl \
         -o ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}

chmod a+x ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}
