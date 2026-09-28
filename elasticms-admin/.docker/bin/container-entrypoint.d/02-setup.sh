#!/usr/bin/env bash
set -eo pipefail

log "INFO" "- Configure ElasticMS Admin Container"

for I in $(find ${APP_CONFIG_DIR}/* | sort)
do

    ELASTICMS_INSTANCE_NAME=$(basename "$I" .${I##*.})
    ELASTICMS_INSTANCE_NAME=${ELASTICMS_INSTANCE_NAME,,}

    log "INFO" "+ Configure ElasticMS [ ${ELASTICMS_INSTANCE_NAME} ] Admin instance"   

    # One subshell per instance. 01-core.sh sources the instance file with set -a,
    # so in a shared shell every key an instance set stayed set for the instances
    # configured after it: an instance that sets no X-Frame-Options, pool size or
    # metrics switch rendered the previous instance's value instead of the image
    # default. The subshell starts each instance from the same environment. A
    # failing step still stops the boot: under set -e the subshell exits with the
    # step's status, and so does this script.
    (
        for FILE in $(find /opt/bin/container-entrypoint.d/elasticms.d -maxdepth 1 -type f -iname '*.sh' | sort)
        do
            ELASTICMS_INSTANCE_CONFIG_FILE=${I} \
            ELASTICMS_INSTANCE_NAME=${ELASTICMS_INSTANCE_NAME} \
            source ${FILE}
        done
    )

done
