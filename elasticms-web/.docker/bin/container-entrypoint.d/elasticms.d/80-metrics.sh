#!/usr/bin/env bash

if [[ ! -z ${EMS_METRIC_ENABLED} ]] && [[ ${EMS_METRIC_ENABLED,,} = true ]]; then

    log "INFO" "+ Clear ElasticMS metrics for [ ${ELASTICMS_INSTANCE_NAME} ] WebSite Domain ..."

    # Not fatal: stale metrics do not keep the service from running.
    if ! ${APP_BIN_DIR}/${ELASTICMS_INSTANCE_NAME} ems:metric:collect --clear --env=${APP_ENV}; then
        log "WARN" "! Something doesn't work with ElasticMS metrics clearing !"
    fi

fi
