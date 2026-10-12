#!/usr/bin/env bash
set -o pipefail

export ELASTICMS_CLI_JOB_ENDTIME=`date +%s`

logLast "───────────────────────────────────────"
logLast "Finished at $(date +"%Y-%m-%d %H:%M:%S") after $((ELASTICMS_CLI_JOB_ENDTIME-ELASTICMS_CLI_JOB_STARTTIME)) seconds"
logLast "───────────────────────────────────────"

# Last, since logLast writes to it: removed first, the file came back with the
# three lines above and outlived every job in /app/tmp.
rm -f ${ELASTICMS_CLI_LOG_TMP_FILE}
