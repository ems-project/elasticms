# syntax=docker/dockerfile:1.15
FROM docker.io/smalswebtech/base-php:8.5-cli AS base

WORKDIR /app

#
# BUILD (mdw)
#

FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS mdw-builder

ARG TIKA_VERSION_ARG

USER root

ENV TIKA_VERSION=${TIKA_VERSION_ARG:-2.9.2} 

ENV NEAREST_TIKA_URL="https://www.apache.org/dyn/closer.cgi/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar?filename=tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar&action=download"
ENV ARCHIVE_TIKA_URL="https://archive.apache.org/dist/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar"
ENV DEFAULT_TIKA_ASC_URL="https://downloads.apache.org/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar.asc"
ENV ARCHIVE_TIKA_ASC_URL="https://archive.apache.org/dist/tika/${TIKA_VERSION}/tika-app-${TIKA_VERSION}.jar.asc"

RUN mkdir -p /rootfs/opt/bin ; \
    apk add --update gnupg wget ; \
    wget -t 10 --max-redirect 1 --retry-connrefused -qO- https://downloads.apache.org/tika/KEYS | gpg --import ; \
    wget -t 10 --max-redirect 1 --retry-connrefused $NEAREST_TIKA_URL -O /tmp/tika-app-${TIKA_VERSION}.jar || rm /tmp/tika-app-${TIKA_VERSION}.jar ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar ]" || wget $ARCHIVE_TIKA_URL -O /tmp/tika-app-${TIKA_VERSION}.jar || rm /tmp/tika-app-${TIKA_VERSION}.jar ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar ]" || exit 1 ; \
    wget -t 10 --max-redirect 1 --retry-connrefused $DEFAULT_TIKA_ASC_URL -O /tmp/tika-app-${TIKA_VERSION}.jar.asc  || rm /tmp/tika-app-${TIKA_VERSION}.jar.asc ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar.asc ]" || wget $ARCHIVE_TIKA_ASC_URL -O /tmp/tika-app-${TIKA_VERSION}.jar.asc || rm /tmp/tika-app-${TIKA_VERSION}.jar.asc ; \
    sh -c "[ -f /tmp/tika-app-${TIKA_VERSION}.jar.asc ]" || exit 1 ; \
    gpg --verify /tmp/tika-app-${TIKA_VERSION}.jar.asc /tmp/tika-app-${TIKA_VERSION}.jar ; \
    cp /tmp/tika-app-${TIKA_VERSION}.jar /rootfs/opt/bin/tika-app.jar

#
# BUILD (app)
#

FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS app-builder

USER root

COPY . /tmpfs

RUN mv /tmpfs/.docker /bootstrap \
    && mv /tmpfs /app/src/elasticms

#
# PRD
#

FROM base AS prd

ARG PHP_EXT_SQLSRV_VERSION_ARG

ENV NODE_ENV=production

ENV APP_DISABLE_DOTENV=true \
    PHP_OPENTELEMETRY_ENABLED=true \
    PHP_BYPASS_INI_DEFAULT_VALUES=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    TMPDIR=/app/tmp 

#ENV PHP_EXT_INSTALL_CUSTOM="sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_odbc"
ENV PHP_EXT_INSTALL_CUSTOM="pdo_odbc"

ENV PHP_PDO_SQLSRV_ENABLED=false \
    PHP_SQLSRV_ENABLED=false

USER root

COPY --from=app-builder --chmod=775 --chown=1001:0 /app /app
COPY --from=app-builder --chmod=775 --chown=1001:0 /bootstrap /opt
COPY --from=mdw-builder --chmod=775 --chown=1001:0 /rootfs/opt /opt

RUN install-php-extensions ${PHP_EXT_INSTALL_CUSTOM} && \
    apk add --update --no-cache chromium \
                                nss \
                                freetype \
                                harfbuzz \
                                ca-certificates \
                                ttf-freefont \
                                openjdk17-jre \
                                tesseract-ocr \
                                msttcorefonts-installer \
                                ttf-dejavu \
                                fontconfig \
                                supervisor \
                                supercronic && \
    update-ms-fonts && \
    fc-cache -f -v && \
    \
    mkdir -p /home/default/Downloads && \
    chown -R 1001:0 /home/default/Downloads && \
    chmod -R ug+rw /home/default/Downloads 

USER 1001

ENTRYPOINT ["dumb-init","--","/opt/bin/container-entrypoint"]

CMD ["/bin/sh", "-ec", "while :; do echo '.'; sleep 5 ; done"]

#
# DEV
#

FROM docker.io/smalswebtech/base-php:8.5-cli-dev AS dev

ARG PHP_EXT_SQLSRV_VERSION_ARG

ENV NODE_ENV=development

ENV APP_DISABLE_DOTENV=true \
    PHP_OPENTELEMETRY_ENABLED=true \
    PHP_BYPASS_INI_DEFAULT_VALUES=true \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser \
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true \
    TMPDIR=/app/tmp 

#ENV PHP_EXT_INSTALL_CUSTOM="sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_sqlsrv-${PHP_EXT_SQLSRV_VERSION_ARG} pdo_odbc"
ENV PHP_EXT_INSTALL_CUSTOM="pdo_odbc"

ENV PHP_PDO_SQLSRV_ENABLED=false \
    PHP_SQLSRV_ENABLED=false

USER root

COPY --from=app-builder --chmod=775 --chown=1001:0 /app /app
COPY --from=app-builder --chmod=775 --chown=1001:0 /bootstrap /opt
COPY --from=mdw-builder --chmod=775 --chown=1001:0 /rootfs/opt /opt

RUN install-php-extensions ${PHP_EXT_INSTALL_CUSTOM} && \
    apk add --update --no-cache chromium \
                                nss \
                                freetype \
                                harfbuzz \
                                ca-certificates \
                                ttf-freefont \
                                openjdk17-jre \
                                tesseract-ocr \
                                msttcorefonts-installer \
                                ttf-dejavu \
                                fontconfig \
                                supervisor \
                                supercronic && \
    update-ms-fonts && \
    fc-cache -f -v && \
    \
    mkdir -p /home/default/Downloads && \
    chown -R 1001:0 /home/default/Downloads && \
    chmod -R ug+rw /home/default/Downloads 

USER 1001

ENTRYPOINT ["dumb-init","--","/opt/bin/container-entrypoint"]

CMD ["/bin/sh", "-ec", "while :; do echo '.'; sleep 5 ; done"]
