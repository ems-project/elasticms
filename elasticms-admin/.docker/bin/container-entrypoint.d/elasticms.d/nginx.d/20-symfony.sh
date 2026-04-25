#!/usr/bin/env bash

gomplate -f /opt/config/nginx/conf.d/app.symfony.conf.gtpl \
         -d "aliases=file://${ALIASES_TEMP_JSON_FILE}?type=application/json" \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.symfony.conf"
