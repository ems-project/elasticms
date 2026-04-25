export ELASTICMS_CLI_LOG_TMP_FILE=$(mktemp)
export ELASTICMS_CLI_VERSION=$(exec /opt/bin/elasticms ems:version)

function logLast() {
    echo "$1" | tee -a "${ELASTICMS_CLI_LOG_TMP_FILE}"
}

function healthcheck () {

    local suffix=${1:-}

    export ELASTICMS_CLI_HEALTHCHECKS_URL=$(eval echo "${ELASTICMS_CLI_HEALTHCHECKS_URL}")

    if [ -z "${ELASTICMS_CLI_HEALTHCHECKS_URL+x}" ]; then
        logLast -n "Reporting healthcheck $suffix ... "
        curl -fSsL --retry 3 -X POST \
            --user-agent "elasticms-cli/${ELASTICMS_CLI_VERSION}" \
            --data-binary "@${ELASTICMS_CLI_LOG_TMP_FILE}" "${ELASTICMS_CLI_HEALTHCHECKS_URL}${suffix}"
    else
        logLast "No ELASTICMS_CLI_HEALTHCHECKS_URL provided. Skipping healthcheck."
    fi

}

function elasticms-command () {

    local -r ELASTICMS_COMMAND="$@"

    logLast "> ELASTICMS_CLI_VERSION: ${ELASTICMS_CLI_VERSION}"
    logLast "> ELASTICMS_COMMAND: ${ELASTICMS_COMMAND}"
    logLast "───────────────────────────────────────"

    healthcheck /start

    exec /opt/bin/elasticms ${ELASTICMS_COMMAND} 2>&1 | tee -a "${ELASTICMS_CLI_LOG_TMP_FILE}"

    if [[ $? == 0 ]]; then
        healthcheck
    else
        healthcheck /fail
    fi

}