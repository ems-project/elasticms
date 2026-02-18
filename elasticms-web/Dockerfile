# syntax=docker/dockerfile:1.15
FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS builder

ENV PHP_INI_SCAN_DIR="/usr/local/etc/php/conf.d" \
    APP_DISABLE_DOTENV=true \
    EMSCO_TIKA_SERVER=http://null \
    MAILER_URL=smtp://null

USER root

COPY . /tmpfs

RUN mv /tmpfs/.docker /bootstrap \
    && mv /tmpfs /app/src/elasticms \
    && mkdir -p /app/src/elasticms/public/bundles /app/src/elasticms/var

#
# Prd
#

FROM docker.io/smalswebtech/base-php:8.5-nginx AS prd

USER root

COPY --from=builder --chmod=775 --chown=1001:0 /bootstrap/ /opt/
COPY --from=builder --chmod=775 --chown=1001:0 /app/ /app/

ENV PHP_BYPASS_INI_DEFAULT_VALUES=true \
    PHP_OPENTELEMETRY_ENABLED=true \
    APP_DISABLE_DOTENV=true \
    EMS_METRIC_PORT="9099"

USER 1001

EXPOSE 9090/tcp

HEALTHCHECK --start-period=1s --interval=10s --timeout=1s --retries=5 \
        CMD curl --fail --header "Host: default.localhost" http://localhost:9000/index.php || exit 1

#
# Dev
#

FROM docker.io/smalswebtech/base-php:8.5-nginx-dev AS dev

USER root

COPY --from=builder --chmod=775 --chown=1001:0 /bootstrap/ /opt/
COPY --from=builder --chmod=775 --chown=1001:0 /app/ /app/

ENV PHP_BYPASS_INI_DEFAULT_VALUES=true \
    PHP_OPENTELEMETRY_ENABLED=true \
    APP_DISABLE_DOTENV=true \
    EMS_METRIC_PORT="9090"

USER 1001

EXPOSE 9090/tcp

HEALTHCHECK --start-period=1s --interval=10s --timeout=1s --retries=5 \
        CMD curl --fail --header "Host: default.localhost" http://localhost:9000/index.php || exit 1