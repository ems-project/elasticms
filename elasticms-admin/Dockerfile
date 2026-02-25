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
    && php /app/src/elasticms/bin/console assets:install /app/src/elasticms/public --symlink --no-interaction --env=prod \
    && rm -rf /app/src/elasticms/var/*

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

EXPOSE 9099/tcp

HEALTHCHECK --start-period=5s --interval=10s --timeout=2s --retries=3 \
        CMD [ $(supervisorctl -c /opt/etc/supervisord.conf status php-fpm nginx | grep -c 'RUNNING') -eq 2 ] || exit 1

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
    EMS_METRIC_PORT="9099"

USER 1001

EXPOSE 9099/tcp

HEALTHCHECK --start-period=5s --interval=10s --timeout=2s --retries=3 \
        CMD [ $(supervisorctl -c /opt/etc/supervisord.conf status php-fpm nginx | grep -c 'RUNNING') -eq 2 ] || exit 1
