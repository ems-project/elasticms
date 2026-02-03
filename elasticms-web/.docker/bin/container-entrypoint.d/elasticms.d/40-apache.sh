#!/usr/bin/env bash

log "INFO" "| Configure ElasticMS Apache VirtualHosts ..."

if [[ ! -z ${APACHE_ENABLED} ]] && [[ ${APACHE_ENABLED,,} = true ]]; then

    log "INFO" "+ Configure [ ${ELASTICMS_INSTANCE_NAME} ] VirtualHost for ElasticMS WebSite on [ ${SERVER_NAME} ]."

    gomplate -f /opt/config/apache2/conf.d/elasticms.conf.gtpl \
             -o /opt/etc/apache2/sites-enabled/${ELASTICMS_INSTANCE_NAME}-app.conf

    cat ${APP_CONFIG_DIR}/${ELASTICMS_INSTANCE_NAME} | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\([a-zA-Z0-9_]*\)\=\(.*\)/        SetEnv \1 \2/g" >> /opt/etc/apache2/sites-enabled/${ELASTICMS_INSTANCE_NAME}-app.env

    if [[ ! -z ${METRICS_ENABLED} ]] && [[ ${METRICS_ENABLED,,} = true ]]; then

        if [ ! -f /opt/etc/apache2/conf.d/__metrics.conf ] ; then

            if [[ ! -z ${EMS_METRIC_ENABLED} ]] && [[ ${EMS_METRIC_ENABLED,,} = true ]]; then

                log "INFO" "+ Configure [ metrics ] VirtualHost for ElasticMS WebSite on [ ${METRICS_VHOST_SERVER_NAME} ]."

                gomplate -f /opt/config/apache2/conf.d/metrics.conf.gtpl \
                         -o /opt/etc/apache2/conf.d/__metrics.conf

                cat ${APP_CONFIG_DIR}/${ELASTICMS_INSTANCE_NAME} | sed '/^\s*$/d' | grep  -v '^#' | sed "s/\([a-zA-Z0-9_]*\)\=\(.*\)/SetEnv \1 \2/g" >> /opt/etc/apache2/conf.d/__metrics.env

            fi

        fi

    fi

fi
