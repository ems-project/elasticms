#!/usr/bin/env bash

log "INFO" "| Create ElasticMS Admin Shell script in ${APP_BIN_DIR}"

gomplate -f /opt/config/sbin/instance.sh.gtpl \
         -o ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}

gomplate -f /opt/config/sbin/messenger-consume-exec.sh.gtpl \
         -o ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}-messenger-consume-exec

gomplate -f /opt/config/sbin/messenger-consume-shutdown.sh.gtpl \
         -o ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}-messenger-consume-shutdown

chmod a+x ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} \
          ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}-messenger-consume-exec \
          ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME}-messenger-consume-shutdown
