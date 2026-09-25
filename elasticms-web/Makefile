#!/usr/bin/make -f

.DEFAULT_GOAL := help
.PHONY: help

help: ## Show help for each of the Makefile recipes.
	@grep -E '(^\S*:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

# —— Environment ——————————————————————————————————————————————————————————————————————————————————————————————————————

CURRENT_UID                 := $(shell id -u)
CURRENT_GID                 := $(shell id -g)

CURRENT_USERNAME            := $(shell id -u -n)
CURRENT_HOMEDIR             := $${HOME}
CURRENT_DIR                 := $(shell pwd)

BUILDER_WORKING_DIR          = /app/src/elasticms
BUILDER_DOCKER_IMAGE_NAME   ?= docker.io/smalswebtech/base-php:8.5-cli-dev

COMPOSER_INSTALL_CMDLINE     = "composer -vvv install --no-interaction --no-suggest --no-scripts -o"
COMPOSER_UPDATE_CMDLINE      = "composer -vvv update --no-interaction --no-suggest --no-scripts -o"
COMPOSER_DIAGNOSE_CMDLINE    = "composer -vvv diagnose --no-interaction --no-scripts"
COMPOSER_SELF_UPDATE_CMDLINE = "composer -vvv self-update --no-interaction"

DOCKER_IMAGE_NAME           ?= docker.io/elasticms/website-skeleton

DOCKER_PLATFORM             ?= linux/amd64
DOCKER_BUILDER              ?= default
DOCKER_OUTPUT               ?= type=image

ENABLE_ATTESTATIONS         ?= true
export ENABLE_ATTESTATIONS

# —— Corporate proxy / custom CA ——————————————————————————————————————————————————————————————————————————————————————
#
# Nothing here takes effect unless the calling environment asks for it, so a
# build with direct internet access (CI, a plain workstation) is unchanged.
#
# Proxy: HTTP(S)_PROXY / NO_PROXY are passed to the image build (as build args;
# `docker bake` reads them from the environment) and to the composer / npm
# containers of build-app.
#
# CA: CUSTOM_CA_BUNDLE (an absolute path to a PEM file) is ADDED to the trust
# store, never substituted for it. At build time it is a secret mounted for the
# downloading steps only and never persisted in the image; CA_BUNDLE_SHA ties the
# cached layer to its content. The composer / npm containers get .cache/ca-bundle.pem
# -- the builder image's own bundle plus the custom CA -- mounted over the system
# bundle, and NODE_EXTRA_CA_CERTS pointing Node at it too -- whether a given Node
# build reads the system store or only its bundled one, it then trusts the CA.
# .cache/ is in .dockerignore, so it never reaches an image.

DOCKER_PROXY_ARGS := \
	--env HTTP_PROXY --env HTTPS_PROXY --env NO_PROXY \
	--env http_proxy --env https_proxy --env no_proxy

DOCKER_BUILD_PROXY_ARGS := \
	--build-arg HTTP_PROXY --build-arg HTTPS_PROXY --build-arg NO_PROXY \
	--build-arg http_proxy --build-arg https_proxy --build-arg no_proxy

# Cache-less rebuild is off by default; pass NO_CACHE=true to force it. Leaving it
# cached is safe: CA_BUNDLE_SHA already rebuilds the CA layer when the CA changes.
NO_CACHE                    ?= false
DOCKER_BUILD_NO_CACHE       := $(if $(filter true,$(NO_CACHE)),--no-cache,)

ifneq ($(strip $(CUSTOM_CA_BUNDLE)),)
export CUSTOM_CA_BUNDLE
export CA_BUNDLE_SHA        := $(shell sha256sum $(CUSTOM_CA_BUNDLE) | cut -d' ' -f1)
CA_RUNTIME_BUNDLE           := $(CURRENT_DIR)/.cache/ca-bundle.pem
CA_PREREQ                   := $(CA_RUNTIME_BUNDLE)
DOCKER_CA_ARGS              := --volume $(CA_RUNTIME_BUNDLE):/etc/ssl/certs/ca-certificates.crt:ro \
                               --env NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt
DOCKER_BUILD_CA_ARGS        := --secret id=ca_bundle,src=$(CUSTOM_CA_BUNDLE) --build-arg CA_BUNDLE_SHA=$(CA_BUNDLE_SHA)
DOCKER_BAKE_CA_ARGS         := --allow=fs.read=$(CUSTOM_CA_BUNDLE)

# Rebuilt whenever the custom CA changes, since it is the prerequisite.
$(CA_RUNTIME_BUNDLE): $(CUSTOM_CA_BUNDLE)
	@mkdir -p $(@D)
	@docker run --rm --entrypoint cat $(BUILDER_DOCKER_IMAGE_NAME) /etc/ssl/certs/ca-certificates.crt > $@.tmp
	@cat $(CUSTOM_CA_BUNDLE) >> $@.tmp
	@mv $@.tmp $@
	@echo "CA bundle: $(CUSTOM_CA_BUNDLE) added to the image trust store -> $@"
endif

# —— ElasticMS build ——————————————————————————————————————————————————————————————————————————————————————————————————

build-app: ## Build ElasticMS Symfony application
	@$(MAKE) composer-diagnose
	@$(MAKE) composer-install

build-image: ## Build ElasticMS Docker images
	@$(MAKE) build-app
	@$(MAKE) docker-build/prd
	@$(MAKE) docker-build/dev

bake-image: ## bake-image DOCKER_PLATFORM="linux/amd64,linux/arm64" DOCKER_BUILDER="cloud-remote" DOCKER_OUTPUT="type=registry" DOCKER_IMAGE_NAME="elasticms/website-skeleton"
	@$(MAKE) build-app
	@$(MAKE) docker-bake/prd
	@$(MAKE) docker-bake/dev

# —— Composer —————————————————————————————————————————————————————————————————————————————————————————————————————————

composer-diagnose: $(CA_PREREQ)
	@echo "\n-- Running Composer diagnose --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_HOME=${CURRENT_HOMEDIR}/.composer \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user root \
		$(DOCKER_PROXY_ARGS) \
		$(DOCKER_CA_ARGS) \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_DIAGNOSE_CMDLINE}

composer-update: $(CA_PREREQ)
	@echo "\n-- Running Composer update --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env PHP_OPENTELEMETRY_ENABLED=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		$(DOCKER_PROXY_ARGS) \
		$(DOCKER_CA_ARGS) \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_UPDATE_CMDLINE}

composer-install: $(CA_PREREQ)
	@echo "\n-- Running Composer install --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env PHP_OPENTELEMETRY_ENABLED=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user ${CURRENT_UID}:${CURRENT_GID} \
		$(DOCKER_PROXY_ARGS) \
		$(DOCKER_CA_ARGS) \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--volume ${CURRENT_DIR}:${BUILDER_WORKING_DIR}:rw \
		--workdir ${BUILDER_WORKING_DIR} \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_INSTALL_CMDLINE}

composer-selfupdate: $(CA_PREREQ)
	@echo "\n-- Running Composer Self-Update --\n"
	@docker run \
		--env PHP_BYPASS_INI_DEFAULT_VALUES=true \
		--env HOME=${CURRENT_HOMEDIR} \
		--env USER=${CURRENT_USERNAME} \
		--env COMPOSER_HOME=${CURRENT_HOMEDIR}/.composer \
		--env COMPOSER_ALLOW_SUPERUSER=1 \
		--env COMPOSER_PROCESS_TIMEOUT=900 \
		--env COMPOSER_MEMORY_LIMIT=-1 \
		--user ${CURRENT_UID}:0 \
		$(DOCKER_PROXY_ARGS) \
		$(DOCKER_CA_ARGS) \
		--volume ${CURRENT_HOMEDIR}:${CURRENT_HOMEDIR}:rw \
		--rm \
		${BUILDER_DOCKER_IMAGE_NAME} \
		bash -c ${COMPOSER_SELF_UPDATE_CMDLINE}

# —— Docker build —————————————————————————————————————————————————————————————————————————————————————————————————————

docker-build/%: ## docker-build/(prd|dev)
	@echo "\n-- Running Docker buildx build --\n"
	@docker buildx build --progress=plain $(DOCKER_BUILD_NO_CACHE) \
		$(DOCKER_BUILD_PROXY_ARGS) \
		$(DOCKER_BUILD_CA_ARGS) \
		--target ${*} \
		--tag ${DOCKER_IMAGE_NAME}:$(if $(filter dev,$*),latest-dev,latest) .

docker-bake/%: ## docker-bake/(prd|dev) DOCKER_PLATFORM="linux/amd64,linux/arm64" DOCKER_BUILDER="cloud-remote" DOCKER_OUTPUT="type=registry" DOCKER_IMAGE_NAME="elasticms/website-skeleton"
	@echo "\n-- Running Docker bake --\n"
	@docker bake --progress=plain $(DOCKER_BUILD_NO_CACHE) \
		$(DOCKER_BAKE_CA_ARGS) \
		--set *.platform=${DOCKER_PLATFORM} \
		--set *.output=${DOCKER_OUTPUT} \
		--builder ${DOCKER_BUILDER} \
		${*}
