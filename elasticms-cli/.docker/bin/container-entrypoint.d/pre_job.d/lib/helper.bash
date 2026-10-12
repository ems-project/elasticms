export ELASTICMS_CLI_LOG_TMP_FILE=$(mktemp)

# The version ems:version prints, read from Composer instead: the command boots
# Symfony (about 0.5 s per job) and appends its Duration and Memory lines, which
# then ended up in the healthcheck User-Agent.
ELASTICMS_CLI_VERSION="$(php -r 'require "/app/src/elasticms/vendor/autoload.php"; echo \Composer\InstalledVersions::getPrettyVersion("elasticms/common-bundle");' 2>/dev/null || true)"
export ELASTICMS_CLI_VERSION

function logLast() {
    echo "$1" | tee -a "${ELASTICMS_CLI_LOG_TMP_FILE}"
}

function healthcheck () {

    local suffix=${1:-}

    export ELASTICMS_CLI_HEALTHCHECKS_URL=$(eval echo "${ELASTICMS_CLI_HEALTHCHECKS_URL}")

    # The test used to be `-z "${URL+x}"`, true only for an unset variable, while
    # 10-init.sh always exports it: no ping was ever sent, configured or not.
    if [ -n "${ELASTICMS_CLI_HEALTHCHECKS_URL}" ]; then
        logLast "Reporting healthcheck ${suffix:-/success} ..."
        # A monitoring endpoint that is down must not fail the job it reports on.
        curl -fSsL --retry 3 -X POST \
            --user-agent "elasticms-cli/${ELASTICMS_CLI_VERSION}" \
            --data-binary "@${ELASTICMS_CLI_LOG_TMP_FILE}" "${ELASTICMS_CLI_HEALTHCHECKS_URL}${suffix}" \
          || logLast "Healthcheck ${suffix:-/success} could not be reported."
    else
        logLast "No ELASTICMS_CLI_HEALTHCHECKS_URL provided. Skipping healthcheck."
    fi

}

function elasticms-command () {

    local -r ELASTICMS_COMMAND="$@"
    local status=0

    logLast "> ELASTICMS_CLI_VERSION: ${ELASTICMS_CLI_VERSION}"
    logLast "> ELASTICMS_COMMAND: ${ELASTICMS_COMMAND}"
    logLast "───────────────────────────────────────"

    healthcheck /start

    # Not `exec`, and the status kept rather than left to `set -e`: either way a
    # failing command ended the job before /fail was reported and before the
    # post_job.d scripts ran.
    # shellcheck disable=SC2086 # the command arrives as one string from cronjob
    /opt/bin/elasticms ${ELASTICMS_COMMAND} 2>&1 | tee -a "${ELASTICMS_CLI_LOG_TMP_FILE}" || status=$?

    if [[ ${status} == 0 ]]; then
        healthcheck
    else
        logLast "Command failed with status ${status}"
        healthcheck /fail
    fi

    return ${status}

}
