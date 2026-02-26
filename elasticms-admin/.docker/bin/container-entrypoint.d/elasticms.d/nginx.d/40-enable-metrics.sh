#!/usr/bin/env bash

if [[ ! -z ${METRICS_ENABLED} ]] && [[ ${METRICS_ENABLED,,} = true ]]; then

    log "INFO" "+ Configure [ nginx/fpm metrics ] VirtualHost for ElasticMS Admin on [ ${METRICS_VHOST_SERVER_NAME} ]."

    gomplate -f /opt/config/nginx/conf.d/metrics.php-fpm.conf.gtpl \
             -o /opt/etc/nginx/conf.d/metrics/${ELASTICMS_INSTANCE_NAME}.conf

    if [ ! -f ${TMPDIR}/default-metrics-is-configured ] ; then

        gomplate -f /opt/config/nginx/conf.d/include.metrics-permissions.conf.gtpl \
                 -o /opt/etc/nginx/conf.d/default.metrics-permissions.conf

        gomplate -f /opt/config/nginx/sites-enabled/default-metrics.conf.gtpl \
                 -o /opt/etc/nginx/sites-enabled/default.metrics.conf

        touch ${TMPDIR}/default-metrics-is-configured

    fi

fi

if [[ ! -z ${EMS_METRIC_ENABLED} ]] && [[ ${EMS_METRIC_ENABLED,,} = true ]]; then

    METRICS_DEFAULT_SERVER="false"

    log "INFO" "+ Configure [ ${ELASTICMS_INSTANCE_NAME} metrics ] VirtualHost for ElasticMS Admin on [ ${NGINX_SERVER_NAME} ]."

    if [ ! -f ${TMPDIR}/elasticms-metrics-${EMS_METRIC_PORT}-is-configured ] ; then
        METRICS_DEFAULT_SERVER="true"
        touch ${TMPDIR}/elasticms-metrics-${EMS_METRIC_PORT}-is-configured
    fi

    export METRICS_DEFAULT_SERVER

    gomplate -f /opt/config/nginx/sites-enabled/elasticms-metrics.conf.gtpl \
             -o /opt/etc/nginx/sites-enabled/${ELASTICMS_INSTANCE_NAME}.metrics.conf

    unset METRICS_DEFAULT_SERVER

fi
