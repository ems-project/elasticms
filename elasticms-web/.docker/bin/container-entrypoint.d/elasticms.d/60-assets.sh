#!/usr/bin/env bash

log "INFO" "+ Running ElasticMS assets installation to ${APACHE_ASSETS_DIR} folder for [ ${ELASTICMS_INSTANCE_NAME} ] WebSite Domain ..."

${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} asset:install ${APACHE_PUBLIC_DIR} --symlink --no-interaction --env=${APP_ENV}

if [ $? -ne 0 ]; then
    log "WARN" "! Something doesn't work with ElasticMS assets installation !"
fi
