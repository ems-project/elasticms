#!/usr/bin/env bash

if [[ ! -z ${METRICS_ENABLED} ]] && [[ ${METRICS_ENABLED,,} = true ]]; then

    if [ ! -f /opt/etc/nginx/sites-enabled/__metrics.conf ] ; then

        log "INFO" "+ Configure [ nginx/fpm metrics ] VirtualHost for ElasticMS Admin on [ ${METRICS_VHOST_SERVER_NAME} ]."

        gomplate -f /opt/config/nginx/sites-enabled/__metrics.conf.gtpl \
                 -o /opt/etc/nginx/sites-enabled/__metrics.conf

    fi

fi

if [[ ! -z ${EMS_METRIC_ENABLED} ]] && [[ ${EMS_METRIC_ENABLED,,} = true ]]; then

    if [ ! -f /opt/etc/nginx/sites-enabled/__metrics_elasticms.conf ] ; then

        log "INFO" "+ Configure [ ${ELASTICMS_INSTANCE_NAME} metrics ] VirtualHost for ElasticMS Admin on [ ${METRICS_VHOST_SERVER_NAME} ]."

        gomplate -f /opt/config/nginx/sites-enabled/elasticms-metrics.conf.gtpl \
                 -o /opt/etc/nginx/sites-enabled/__metrics_elasticms.conf

    fi

fi
