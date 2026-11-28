#!/usr/bin/env bash
set -o pipefail

source "${ELASTICMS_CLI_PRE_JOB_PATH}/lib/helper.bash"

export ELASTICMS_CLI_JOB_STARTTIME=`date +%s`

logLast "───────────────────────────────────────"
logLast "Starting at $(date +"%Y-%m-%d %H:%M:%S")"
logLast "───────────────────────────────────────"