#!/usr/bin/env bash
#
# Smoke test of a built image: every way the image is started, and the PHP
# configuration it inherits from base-php. CI runs it before anything is
# published; 7.3.8 shipped without it and did not start at all.
#
#   tests/docker/smoke.sh [IMAGE]      (default: elasticms/cli:latest)

set -uo pipefail

IMAGE="${1:-elasticms/cli:latest}"
RUN=(docker run --rm --pull=never --tmpfs /opt/etc --tmpfs /app/tmp)
FAILED=0
CONTAINERS=()

cleanup() {
	[ ${#CONTAINERS[@]} -eq 0 ] || docker rm -f "${CONTAINERS[@]}" >/dev/null 2>&1
}
trap cleanup EXIT

check() {
	local name=$1 output=$2 pattern=$3
	if grep -q -E -- "$pattern" <<<"$output"; then
		echo "ok     - $name"
	else
		echo "not ok - $name (expected /$pattern/)"
		sed 's/^/         | /' <<<"$output" | tail -15
		FAILED=1
	fi
}

strip() { sed 's/\x1b\[[0-9;]*m//g'; }

# The default command keeps the container up.
CID=$(docker run -d --pull=never --tmpfs /opt/etc --tmpfs /app/tmp "$IMAGE")
CONTAINERS+=("$CID")
sleep 10
check "the default command keeps the container running" "$(docker inspect -f '{{.State.Status}}' "$CID")" '^running$'
check "the default command configures PHP" "$(docker logs "$CID" 2>&1 | strip)" 'PHP Container configured successfully'

# A Symfony command as first argument runs through bin/console.
OUT=$("${RUN[@]}" "$IMAGE" ems:version 2>&1 | strip; echo "status=${PIPESTATUS[0]}")
check "a Symfony command runs" "$OUT" 'status=0$'

# The extensions the image adds load, and a PHP setting given through the
# environment is applied without reaching the application.
OUT=$("${RUN[@]}" -e PHP_MEMORY_LIMIT=300M "$IMAGE" \
	php -r 'echo implode(",", array_intersect(["PDO_ODBC", "opentelemetry"], get_loaded_extensions())), PHP_EOL;
		echo "memory=", ini_get("memory_limit"), " env=", var_export(getenv("PHP_MEMORY_LIMIT"), true), PHP_EOL;' 2>&1 | strip)
check "pdo_odbc and opentelemetry are loaded" "$OUT" '^PDO_ODBC,opentelemetry$'
check "a PHP setting is applied, and the application does not see it" "$OUT" '^memory=300M env=false$'

# The crontab mode schedules the command (every five seconds here).
CID=$(docker run -d --pull=never --tmpfs /opt/etc --tmpfs /app/tmp \
	-e 'ELASTICMS_CLI_CROND_SCHEDULE=*/5 * * * * * *' "$IMAGE" crontab)
CONTAINERS+=("$CID")
sleep 20
check "the crontab mode runs the scheduled job" "$(docker logs "$CID" 2>&1 | strip)" 'job succeeded'

# A command given after `crontab` replaces the default one. The early hooks
# that set the default run before the crontab script, in base-php's entrypoint.
CID=$(docker run -d --pull=never --tmpfs /opt/etc --tmpfs /app/tmp \
	-e 'ELASTICMS_CLI_CROND_SCHEDULE=*/5 * * * * * *' "$IMAGE" crontab list)
CONTAINERS+=("$CID")
sleep 20
check "the crontab mode runs the command it is given" "$(docker logs "$CID" 2>&1 | strip)" '> ELASTICMS_COMMAND: list'

# The cronjob mode runs one job and reports its status.
OUT=$("${RUN[@]}" "$IMAGE" cronjob ems:version 2>&1 | strip; echo "status=${PIPESTATUS[0]}")
check "a cronjob succeeds" "$OUT" 'status=0$'
check "a job logs the version on one line" "$OUT" '^> ELASTICMS_CLI_VERSION: [^ ]+$'

# A failing job fails the container, still reports /fail and still runs the
# post-job scripts. The healthcheck endpoint is PHP's built-in server, inside
# the container, which logs each request it gets.
OUT=$("${RUN[@]}" -e ELASTICMS_CLI_HEALTHCHECKS_URL=http://127.0.0.1:8099/hc --entrypoint sh "$IMAGE" -c '
	php -S 127.0.0.1:8099 -t /app/tmp 2>/app/tmp/hc.log &
	sleep 1
	/opt/bin/container-entrypoint cronjob no:such-command
	echo "status=$?"
	cat /app/tmp/hc.log' 2>&1 | strip)
check "a failing cronjob exits non-zero" "$OUT" '^status=[1-9]'
check "a failing cronjob reports /start" "$OUT" 'POST /hc/start'
check "a failing cronjob reports /fail" "$OUT" 'POST /hc/fail'
check "a failing cronjob runs the post-job scripts" "$OUT" '^Finished at '

exit $FAILED
