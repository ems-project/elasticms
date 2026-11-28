#!/usr/bin/env bash

log "INFO" "| Configure ElasticMS Admin Jobs"

if [[ ! -z ${JOBS_ENABLED} ]] && [[ ${JOBS_ENABLED,,} = true ]]; then

    log "INFO" "+ Use Supervisor for running ElasticMS Admin Jobs ..."

    gomplate -f /opt/config/supervisor/eventlistener.ini.gtpl \
        -o /opt/etc/supervisor.d/${ELASTICMS_INSTANCE_NAME}.ini

    gomplate -f /opt/config/sbin/ems-job-run.sh.gtpl \
        -o ${APP_BIN_DIR}/ems-jobs/${ELASTICMS_INSTANCE_NAME}

    chmod a+x ${APP_BIN_DIR}/ems-jobs/${ELASTICMS_INSTANCE_NAME}

else

    log "INFO" "+ Use PHP-FPM for running ElasticMS Admin Jobs ..."

fi
