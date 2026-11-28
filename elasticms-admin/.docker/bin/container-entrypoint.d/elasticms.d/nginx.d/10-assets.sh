#!/usr/bin/env bash

gomplate -f /opt/config/nginx/conf.d/app.assets.conf.gtpl \
         -d "aliases=file://${ALIASES_TEMP_JSON_FILE}?type=application/json" \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.assets.conf"

gomplate -f /opt/config/nginx/conf.d/include.bundles.conf.gtpl \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.bundles.conf"

gomplate -f /opt/config/nginx/conf.d/include.static-files.conf.gtpl \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.static-files.conf"
