#!/usr/bin/env bash
set -eo pipefail

log "INFO" "- Configure ElasticMS Admin Container"

for I in $(find ${APP_CONFIG_DIR}/* | sort)
do

    ELASTICMS_INSTANCE_NAME=$(basename "$I" .${I##*.})
    ELASTICMS_INSTANCE_NAME=${ELASTICMS_INSTANCE_NAME,,}

    log "INFO" "+ Configure ElasticMS [ ${ELASTICMS_INSTANCE_NAME} ] Admin instance"   

    for FILE in $(find /opt/bin/container-entrypoint.d/elasticms.d -maxdepth 1 -type f -iname '*.sh' | sort)
    do
        ELASTICMS_INSTANCE_CONFIG_FILE=${I} \
        ELASTICMS_INSTANCE_NAME=${ELASTICMS_INSTANCE_NAME} \
        source ${FILE}
    done

done
