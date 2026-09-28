#!/usr/bin/env bash
set -eo pipefail

# Refuses to start when two instances of this container read the same Redis
# messenger stream. Runs after 01-install.sh, which writes the instance files,
# and before 02-setup.sh configures anything.
#
# A stream is read by every worker of every instance pointing at it, whatever
# instance sent the message. A JobMessage carries only a job id, so another
# instance's worker ran *its own* job with that id, against its own database.
# Measured on the demo: ten messages sent by one instance were read by the
# workers of both.
#
# Only instances that run workers here (MESSENGER_ENABLED) can take another's
# messages, so a stream shared by instances none of which consumes is let
# through.

log "INFO" "- Check that no two ElasticMS instances share a messenger stream"

# The values each instance resolves to: its own file over the container's
# environment, over the application's default. Each file in a subshell, so
# nothing of one reaches the next.
streams=$(
    for I in $(find ${APP_CONFIG_DIR}/* | sort); do
        (
            set -a
            source "$I"
            set +a
            name=$(basename "$I" .${I##*.})
            printf '%s\t%s\t%s\n' "${name,,}" "${MESSENGER_ENABLED:-true}" \
                "${MESSENGER_TRANSPORT_DSN:-redis://localhost:6379/messages}"
        )
    done | python3 -c '
import re, sys
from urllib.parse import parse_qsl, urlsplit

# The stream a Redis DSN designates, read the way Symfony reads it: hosts,
# database index and stream name. Credentials are left out: this ends up in
# the log.
def stream_of(dsn):
    if not re.match(r"(redis|rediss|valkey|valkeys):", dsn):
        return None
    hosts, options, path = [], {}, ""
    for part in dsn.split(","):
        url = urlsplit(re.sub(r"^[a-z]+:(//)?(?:[^@/]*@)?", "x://", part))
        query = dict(parse_qsl(url.query, keep_blank_values=True))
        options.update(query)
        hosts += [k[5:-1] for k in query if k.startswith("host[")]
        if url.hostname:
            hosts.append("%s:%s" % (url.hostname, url.port or 6379))
        path = url.path or path
    segments = path.rstrip("/").split("/")
    stream = segments[1] if len(segments) > 1 and segments[1] else options.get("stream", "messages")
    return "%s, database %s, stream \"%s\"" % (",".join(sorted(hosts)) or path, options.get("dbindex", "0"), stream)

groups = {}
for line in sys.stdin:
    name, enabled, dsn = line.rstrip("\n").split("\t", 2)
    stream = stream_of(dsn)
    if stream:
        groups.setdefault(stream, []).append((name, enabled.lower() == "true"))

for stream, instances in sorted(groups.items()):
    if len(instances) > 1 and any(consumes for _, consumes in instances):
        print("%s\t%s" % (stream, " ".join(name for name, _ in instances)))
'
)

if [ -n "$streams" ]; then
    while IFS=$'\t' read -r stream instances; do
        log "ERROR" "! ElasticMS instances [ ${instances// /, } ] share one messenger stream: ${stream}."
    done <<<"$streams"
    log "ERROR" "! Each instance's workers would take the others' messages and run their jobs against the wrong database."
    log "ERROR" "! Give each instance its own stream in MESSENGER_TRANSPORT_DSN, e.g. redis://<host>:6379/<instance>, or its own Redis database (?dbindex=)."
    exit 1
fi
