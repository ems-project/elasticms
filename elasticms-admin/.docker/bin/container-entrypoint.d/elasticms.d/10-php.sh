#!/usr/bin/env bash

ELASTICMS_INSTANCE_CONFIG_JSON_FILE="${APP_CONFIG_JSON_DIR}/${ELASTICMS_INSTANCE_NAME}.json"

while IFS= read -r line || [[ -n "$line" ]]; do
    [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue

    key="${line%%=*}"
    value="${line#*=}"

    if [[ $value =~ ^\".*\"$ ]] || [[ $value =~ ^\'.*\'$ ]]; then
        value="${value:1:-1}"
    fi

    printf '%s=%s\n' "$key" "$value"
done < "${ELASTICMS_INSTANCE_CONFIG_FILE}" | jq -Rn '
  [ inputs
    | capture("^(?<key>[^=]+)=(?<value>.*)$")
  ]
  | from_entries
' > "${ELASTICMS_INSTANCE_CONFIG_JSON_FILE}"

gomplate -f /opt/config/php/php-fpm.d/elasticms.conf.gtpl \
         -d "variables=file://${ELASTICMS_INSTANCE_CONFIG_JSON_FILE}?type=application/json" \
         -o "/opt/etc/php/php-fpm.d/${ELASTICMS_INSTANCE_NAME}.conf"
