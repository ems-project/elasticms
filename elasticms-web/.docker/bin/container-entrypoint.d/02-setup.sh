#!/usr/bin/env bash
set -eo pipefail

log "INFO" "- Configure ElasticMS WebSite Container"

for I in $(find ${APP_CONFIG_DIR}/* | sort)
do

    log "INFO" "+ Configure ElasticMS [$(basename "$I" .${I##*.})] WebSite instance"

    for FILE in $(find /opt/bin/container-entrypoint.d/elasticms.d -iname \*.sh | sort)
    do
        ELASTICMS_INSTANCE_NAME=$(basename "$I" .${I##*.}) \
        ELASTICMS_INSTANCE_CONFIG_FILE=${I} \
        source ${FILE}
    done

done
