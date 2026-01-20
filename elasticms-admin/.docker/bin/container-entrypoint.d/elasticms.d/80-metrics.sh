#!/usr/bin/env bash

if [[ ! -z ${EMS_METRIC_ENABLED} ]] && [[ ${EMS_METRIC_ENABLED,,} = true ]]; then

    log "INFO" "+ Clear Elasticms metrics for [ ${ELASTICMS_INSTANCE_NAME} ] CMS Domain ..."

    ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} ems:metric:collect --clear --env=${APP_ENV}

    if [ $? -ne 0 ]; then
        log "WARN" "! Something doesn't work with Elasticms metrics clearing !"
    fi

fi