#!/usr/bin/env bash
set -eo pipefail

# Warns when two instances of this container share a Symfony cache. Runs after
# 01-install.sh, which writes the instance files, and before 02-setup.sh.
#
# APP_CACHE_DIR is set for the whole container, and Symfony appends only the
# environment name to it. The intended setup is that each instance redefines it
# ('${APP_CACHE_DIR}/<instance>'); an instance that does not, and runs the same
# APP_ENV as another, shares with it the compiled container and the filesystem
# cache pools -- each can then serve the other's configuration or cached data,
# with nothing failing. A warning rather than a refusal: the setup is legitimate
# when the two instances are meant to be the same application.

log "INFO" "- Check that no two ElasticMS instances share a Symfony cache"

# The values each instance resolves to: its own file over the container's
# environment. Each file in a subshell, so nothing of one reaches the next.
shared=$(
    for I in $(find ${APP_CONFIG_DIR}/* | sort); do
        (
            set -a
            source "$I"
            set +a
            name=$(basename "$I" .${I##*.})
            printf '%s/%s\t%s\n' "${APP_CACHE_DIR%/}" "${APP_ENV}" "${name,,}"
        )
    done | awk -F '\t' '{ n[$1]++; names[$1] = names[$1] (names[$1] ? ", " : "") $2 }
                        END { for (d in n) if (n[d] > 1) print d "\t" names[d] }'
)

while IFS=$'\t' read -r dir instances; do
    [ -n "$dir" ] || continue
    log "WARN" "! ElasticMS instances [ ${instances} ] share one Symfony cache: ${dir}."
    log "WARN" "! Unless they are the same application, give each its own, e.g. APP_CACHE_DIR='\${APP_CACHE_DIR}/<instance>' in its configuration."
done <<<"$shared"

true
