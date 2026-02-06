#!/usr/bin/env bash
set -eo pipefail

log "INFO" "- Configure ElasticMS Admin Container"

ALIASES=()

for I in $(find ${APP_CONFIG_DIR}/* | sort)
do

    log "INFO" "+ Configure ElasticMS [$(basename "$I" .${I##*.})] Admin instance"

    ALIAS=$( grep -E '^ALIAS=' "$I" | head -n1 | sed -E "s/^ALIAS=['\"]?(.*)['\"]?$/\1/" | tr -d "\'\"" )

    if [[ -n "$ALIAS" ]]; then
        ALIASES+=("$ALIAS")
    fi

    for FILE in $(find /opt/bin/container-entrypoint.d/elasticms.d -iname \*.sh | sort)
    do
        ELASTICMS_INSTANCE_NAME=$(basename "$I" .${I##*.}) \
        ELASTICMS_INSTANCE_CONFIG_FILE=${I} \
        source ${FILE}
    done

done

ALIASES_UNIQ=($(printf '%s\n' "${ALIASES[@]}" | sort -u))
export ALIASES_JSON=$(printf '%s\n' "${ALIASES_UNIQ[@]}" | jq -R . | jq -s -c .)

gomplate -f /opt/config/nginx/conf.d/symfony-script-name.map.gtpl \
         -d aliases=env:/ALIASES_JSON?type=application/json \
         -o /opt/etc/nginx/conf.d/symfony-script-name.map

unset ALIASES ALIASES_UNIQ ALIASES_JSON
