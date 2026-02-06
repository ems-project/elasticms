#!/usr/bin/env bash

log "INFO" "| Configure ElasticMS Nginx VirtualHosts ..."

if [[ ! -z ${NGINX_ENABLED} ]] && [[ ${NGINX_ENABLED,,} = true ]]; then

    log "INFO" "+ Configure [ ${ELASTICMS_INSTANCE_NAME} ] VirtualHost for ElasticMS Admin on [ ${SERVER_NAME} ]."

    gomplate -f /opt/config/nginx/sites-enabled/elasticms.conf.gtpl \
             -o /opt/etc/nginx/sites-enabled/${ELASTICMS_INSTANCE_NAME}.conf

    gomplate -f /opt/config/nginx/conf.d/security-headers.conf.gtpl \
             -o /opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.security-headers.conf;

    cat ${APP_CONFIG_DIR}/${ELASTICMS_INSTANCE_NAME} | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\([a-zA-Z0-9_]*\)\=\(.*\)/fastcgi_param \1 \2;/g" >> /opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.fastcgi_params

    if [[ ! -z ${EMS_METRIC_ENABLED} ]] && [[ ${EMS_METRIC_ENABLED,,} = true ]]; then

        if [ ! -f /opt/etc/nginx/sites-enabled/__metrics_elasticms.conf ] ; then

            log "INFO" "+ Configure [ ${ELASTICMS_INSTANCE_NAME} metrics ] VirtualHost for ElasticMS WebSite on [ ${METRICS_VHOST_SERVER_NAME} ]."

            gomplate -f /opt/config/nginx/sites-enabled/elasticms-metrics.conf.gtpl \
                     -o /opt/etc/nginx/sites-enabled/__metrics_elasticms.conf

        fi

    fi

    if [[ ! -z ${METRICS_ENABLED} ]] && [[ ${METRICS_ENABLED,,} = true ]]; then

        if [ ! -f /opt/etc/nginx/sites-enabled/__metrics.conf ] ; then

            log "INFO" "+ Configure [ nginx/fpm metrics ] VirtualHost for ElasticMS WebSite on [ ${METRICS_VHOST_SERVER_NAME} ]."

            gomplate -f /opt/config/nginx/sites-enabled/__metrics.conf.gtpl \
                     -o /opt/etc/nginx/sites-enabled/__metrics.conf

        fi

    fi

fi

