#!/usr/bin/env bash

echo ---
echo Running ems-jobs for [ {{ .Env.ELASTICMS_INSTANCE_NAME }} ]
echo ---

/opt/sbin/{{ .Env.ELASTICMS_INSTANCE_NAME }} ems:job:run ${JOBS_OPTS}
