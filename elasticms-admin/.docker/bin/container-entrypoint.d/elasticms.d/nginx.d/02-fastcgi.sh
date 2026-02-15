#!/usr/bin/env bash

{
  grep -E '^ALIAS=' "${ELASTICMS_INSTANCE_CONFIG_FILE}" || echo 'ALIAS='
} \
| head -n1 \
| sed -E "s/^ALIAS=['\"]?(.*)['\"]?$/\1/" \
| tr -d "'\"" \
| tr ' ' '\n' \
| sed '/^$/d' \
| sort -u \
| jq -R . \
| jq -s -c . \
> "${ALIASES_TEMP_JSON_FILE}"

gomplate -f /opt/config/nginx/conf.d/include.fastcgi.conf.gtpl \
         -o "/opt/etc/nginx/conf.d/${ELASTICMS_INSTANCE_NAME}.fastcgi.conf"
