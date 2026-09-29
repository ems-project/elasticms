#!/usr/bin/env bash

ELASTICMS_INSTANCE_CONFIG_JSON_FILE="${APP_CONFIG_JSON_DIR}/${ELASTICMS_INSTANCE_NAME}.json"

while IFS= read -r line || [[ -n "$line" ]]; do
    [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue

    key="${line%%=*}"
    value="${line#*=}"

    # Every key becomes an env[] entry of the php-fpm pool, i.e. reaches the
    # application. The instance configuration also carries the image's own
    # settings -- per-instance infrastructure knobs, and with no configuration
    # file the whole environment, base-php php.ini settings and AWS credentials
    # included. base-php unsets those before php-fpm starts and hands the late
    # hooks their names in CLEANUP_VAR_LIST; leave them out here too, or env[]
    # brings back exactly what that cleanup removed. They are still sourced by
    # 01-core.sh, so the templates keep seeing them. With an older base-php that
    # does not provide the list, nothing is left out, as before.
    [[ -n "${CLEANUP_VAR_LIST:-}" && ":${CLEANUP_VAR_LIST}:" == *":${key}:"* ]] && continue

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
