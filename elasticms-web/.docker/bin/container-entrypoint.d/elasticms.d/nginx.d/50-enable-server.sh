#!/usr/bin/env bash

gomplate -f /opt/config/nginx/sites-enabled/elasticms.conf.gtpl \
         -d "environments=file://${NGINX_ENVIRONMENTS_TEMP_JSON_FILE}?type=application/json" \
         -o "/opt/etc/nginx/sites-enabled/${ELASTICMS_INSTANCE_NAME}.conf"
