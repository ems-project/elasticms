#!/usr/bin/make -f

-include ./docker/.env

PWD					   = $(shell pwd)
DOCKER_USER    ?= $(shell id -u)
DOCKER_COMPOSE = docker compose --project-directory=docker

PORT_admin 			= 8881
PORT_web 			= 8882
PORT_cli 			= 8883

RUN_ADMIN			= php ${PWD}/elasticms-admin/bin/console --no-debug
RUN_CLI				= php ${PWD}/elasticms-cli/bin/console --no-debug
RUN_WEB				= php ${PWD}/elasticms-web/bin/console --no-debug
RUN_POSTGRES		= docker compose --project-directory=docker exec -i -u ${DOCKER_USER}:0 -e PGUSER=postgres -e PGPASSWORD=adminpg postgres
NPM_CMD          	= "${NPM_EXTRA_CMD} npm $* --ignore-scripts"
RUN_DEMO_NPM		= docker run -u ${DOCKER_USER}:0 --rm -it -v ${PWD}/demo:/opt/src --workdir /opt/src elasticms/base-php:8.4-cli-dev sh -c ${NPM_CMD}
RUN_ADMIN_UI_NPM 	= docker run -u ${DOCKER_USER}:0 --rm -p 5173:5173 -it -v ${PWD}/EMS/admin-ui-bundle:/opt/src --workdir /opt/src/assets elasticms/base-php:8.4-cli-dev sh -c ${NPM_CMD}
OTEL_ENABLED 		?= false

.DEFAULT_GOAL := help
.PHONY: help demo docs docker/sandbox.passwd

help: # Show help for each of the Makefile recipes.
	@echo "EMS Monorepo"
	@echo "---------------------------"
	@echo "DOCKER_USER:   ${DOCKER_USER}"
	@echo "OTEL enabled:  ${OTEL_ENABLED}"
	@echo "NPM_EXTRA_CMD: ${NPM_EXTRA_CMD}"
	@echo "ADMIN:         http://localhost:8881"
	@echo "WEB:           http://localhost:8882"
	@echo "KIBANA:        http://kibana.localhost"
	@echo "MINIO:         http://minio.localhost"
	@echo "MAIL:          http://mailserver.localhost"
	@echo "---------------------------"
	@echo ""
	@echo "Usage: make [target]"
	@echo "Targets:"
	@grep -E '(^\S*:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Mono —————————————————————————————————————————————————————————————————————————————————————————————————————————————
init: ## init mono repo (copy .env)
	@cp -fp ./docker/.env.dist ./docker/.env
	@cp -fp ./docker/sandbox.env.dist ./docker/sandbox.env
	@$(MAKE) -s docker/sandbox.passwd
	@cp -fp ./elasticms-admin/.env.dist ./elasticms-admin/.env
	@cp -fp ./elasticms-admin/.env.local.dist ./elasticms-admin/.env.local
	@cp -fp ./elasticms-web/.env.dist ./elasticms-web/.env
	@cp -fp ./elasticms-web/.env.local.dist ./elasticms-web/.env.local
start: ## start docker, admin server, web server
	@$(DOCKER_COMPOSE) --profile=ems up -d
	@$(MAKE) -s server-start/admin
	@$(MAKE) -s server-start/web
	cd elasticms-admin && symfony local:run -d php bin/console messenger:consume async -vvv
start/%: ## start/(mariadb|keycloak|grafana|redis-commander)
	@$(DOCKER_COMPOSE) --profile=${*} up -d --force-recreate
start/sandbox: docker/sandbox.passwd ## start/sandbox
	@$(DOCKER_COMPOSE) --profile=sandbox up -d --force-recreate
start/mcp: ## start/mcp
	@$(DOCKER_COMPOSE) --profile=mcp up -d --force-recreate
stop: ## stop docker, admin server, web server
	@$(MAKE) -s server-stop/admin
	@$(MAKE) -s server-stop/web
	@$(DOCKER_COMPOSE) --profile=all down
stop/%: ## stop/(mariadb|keycloak|grafana|redis-commander)
	@$(DOCKER_COMPOSE) --profile=${*} down
check: ## run all checks
	@composer monorepo-validate
	@composer rector
	@composer phpall
	@composer lint
	@$(MAKE) build-translations
cache-clear: ## cache clear
	@$(RUN_ADMIN) c:cl
	@$(RUN_CLI) c:cl
	@$(RUN_WEB) c:cl
status: ## status
	@docker ps --filter="label=elasticMS" --format "table {{.Label \"com.docker.compose.service\"}}\t{{.Status}}\t{{.Ports}}"
pull: ## Pull service images
	@$(DOCKER_COMPOSE) pull
sandbox: docker/sandbox.passwd ## open a terminal in a development sandbox container
	@$(DOCKER_COMPOSE) exec sandbox sh -lc 'exec "$${SHELL:-bash}"'

docker/sandbox.passwd:
	@mkdir -p ./docker
	@printf '%s\n' \
		'root:x:0:0:root:/root:/bin/sh' \
		'bin:x:1:1:bin:/bin:/sbin/nologin' \
		'daemon:x:2:2:daemon:/sbin:/sbin/nologin' \
		'lp:x:4:7:lp:/var/spool/lpd:/sbin/nologin' \
		'sync:x:5:0:sync:/bin:/bin/sync' \
		'shutdown:x:6:0:shutdown:/sbin:/sbin/shutdown' \
		'halt:x:7:0:halt:/sbin:/sbin/halt' \
		'mail:x:8:12:mail:/var/mail:/sbin/nologin' \
		'news:x:9:13:news:/usr/lib/news:/sbin:/sbin/nologin' \
		'uucp:x:10:14:uucp:/var/spool/uucppublic:/sbin/nologin' \
		'cron:x:16:16:cron:/var/spool/cron:/sbin/nologin' \
		'ftp:x:21:21::/var/lib/ftp:/sbin/nologin' \
		'sshd:x:22:22:sshd:/dev/null:/sbin/nologin' \
		'games:x:35:35:games:/usr/games:/sbin/nologin' \
		'ntp:x:123:123:NTP:/var/empty:/sbin/nologin' \
		'guest:x:405:100:guest:/dev/null:/sbin/nologin' \
		'nobody:x:65534:65534:nobody:/:/sbin/nologin' \
		'www-data:x:82:82::/home/www-data:/sbin/nologin' \
		'postgres:x:70:70:PostgreSQL user:/var/lib/postgresql:/bin/sh' \
		'default:x:$(DOCKER_USER):0:default:/home/default:/bin/bash' \
		> $@

## —— Symfony server ———————————————————————————————————————————————————————————————————————————————————————————————————
server-start/%: ## server-start/(admin|web|cli)
	@if [ "$(OTEL_ENABLED)" = "true" ]; then \
		env \
		OTEL_PHP_AUTOLOAD_ENABLED=true \
		OTEL_PHP_FIBERS_ENABLED=true \
		OTEL_SERVICE_NAME=demo-ems-$(*) \
		OTEL_TRACES_EXPORTER=otlp \
		OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf \
		OTEL_EXPORTER_OTLP_ENDPOINT=http://apm-server.localhost \
		OTEL_RESOURCE_ATTRIBUTES=deployment.environment=dev \
		OTEL_TRACES_SAMPLER=always_on \
		OTEL_LOG_LEVEL=debug \
		OTEL_PHP_LOG_DESTINATION=stderr \
		OTEL_PHP_AUTOLOAD_PATH=$(MAKEFILE_DIR)/vendor/autoload.php \
		symfony server:start --dir=elasticms-$* -d --port=$(PORT_$(*)) --no-tls --allow-all-ip; \
	else \
		symfony server:start --dir=elasticms-$* -d --port=$(PORT_$(*)) --no-tls --allow-all-ip; \
	fi
server-stop/%: ## server-stop/(admin|web|cli)
	symfony server:stop --dir=elasticms-${*}
server-log/%: ## server-log/(admin|web|cli)
	symfony server:log --dir=elasticms-${*}
server-status/%: ## server-log/(admin|web|cli)
	symfony server:status --dir=elasticms-${*}
server-restart: ## server-restart
	@$(MAKE) -s server-stop/admin
	@$(MAKE) -s server-stop/web
	@$(MAKE) -s server-start/admin
	@$(MAKE) -s server-start/web
	cd elasticms-admin && symfony local:run -d php bin/console messenger:consume async -vvv

## —— Docker --------———————————————————————————————————————————————————————————————————————————————————————————————————
docker-images: ## List images
	@docker ps --filter="label=elasticMS" --format "table {{.Label \"com.docker.compose.service\"}}\t{{.Image}}"
docker-logs/%: ## logs by profile
	@$(DOCKER_COMPOSE) --profile=${*} logs -f
docker-logs: ## logs
	@$(DOCKER_COMPOSE) logs -f

## —— assets ————————————————————————————————————————————————————————————————————————————————————————————————————————————
assets-npm/%: ## npm run in AdminUIBundle
	@$(RUN_ADMIN_UI_NPM) $*
assets-install: ## Install NPM dependencies in AdminUIBundle assets
	@$(MAKE) -s assets-npm/"install"
assets-build: ## build AdminUIBundle assets
	@$(MAKE) -s assets-npm/"run build"
assets-clean: ## remove AdminUIBundle assets
	rm -Rf EMS/admin-ui-bundle/public
assets-dev: ## Start an AdminUIBundle Vite server
	@$(MAKE) -s assets-clean
	@$(MAKE) -s assets-npm/"run dev-host"

## —— Doc ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
docs: ## serve docs
	npm run --prefix ./docs docs:dev
docs-build: ## build docs
	npm run --prefix ./docs docs:build
docs-format: ## format docs
	npm run --prefix ./docs docs:format
docs-lint: ## lint docs
	npm run --prefix ./docs docs:lint
docs-init: ## init docs
	npm install --prefix ./docs

## —— Build ————————————————————————————————————————————————————————————————————————————————————————————————————————————
build-translations: ## build translations
	@php build/translations en EMSCoreBundle --write --format=yml -d emsco-core
	@php build/translations fr EMSCoreBundle --write --format=yml -d emsco-core
	@php build/translations nl EMSCoreBundle --write --format=yml -d emsco-core
	@php build/translations en EMSAdminUIBundle --write --format=yml
	@php build/translations fr EMSAdminUIBundle --write --format=yml
	@php build/translations nl EMSAdminUIBundle --write --format=yml
	@$(RUN_CLI) translation:extract  fr --force --format=yaml --domain=messages
	@$(RUN_CLI) translation:extract  nl --force --format=yaml --domain=messages
	@$(RUN_CLI) translation:extract  de --force --format=yaml --domain=messages
	@$(RUN_CLI) translation:extract  en --force --format=yaml --domain=messages

## —— Database —————————————————————————————————————————————————————————————————————————————————————————————————————————
db-migrate: ## run doctrine migrations
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction
db-load/%: ## make db-load/"db_example" DUMP=../../dumps.sql
	@$(RUN_POSTGRES) psql -U ${*} < ${DUMP}
db-dump/%: ## db-dump/"db_example" SCHEMA="schema_example_adm"
	@$(RUN_POSTGRES) pg_dump ${*} -w --clean -Fp -O --schema=${SCHEMA} | sed "/^\(DROP\|ALTER\|CREATE\) SCHEMA.*\$$/d" > dump_demo_$$(date +%Y%m%d%H%M%S).sql
db-drop/%: ## db-drop/"db_example"
	@$(RUN_POSTGRES) psql -c "DROP DATABASE IF EXISTS ${*};"
	@$(RUN_POSTGRES) psql -c "DROP USER IF EXISTS ${*};"
db-schema-rename/%: ## db-schema-rename/"db_example" FROM="schema_from" TO="schema_to"
	@$(RUN_POSTGRES) psql -d ${*} -c "ALTER SCHEMA ${FROM} RENAME TO ${TO};"
	@$(RUN_POSTGRES) psql -d ${*} -c "ALTER USER ${*} SET search_path TO ${TO};"
db-create/%: ## db-create/"db_example" SCHEMA="schema_example_adm"
	@$(RUN_POSTGRES) psql -c "CREATE USER ${*} WITH ENCRYPTED PASSWORD '${*}';"
	@$(RUN_POSTGRES) psql -c "CREATE DATABASE ${*} WITH OWNER ${*};"
	@$(RUN_POSTGRES) psql -c "GRANT ALL PRIVILEGES ON DATABASE ${*} TO ${*};"
	@$(RUN_POSTGRES) psql -d ${*} -c "ALTER SCHEMA public OWNER TO ${*};"
	@$(RUN_POSTGRES) psql -d ${*} -c "ALTER SCHEMA public RENAME TO ${SCHEMA};"
	@$(RUN_POSTGRES) psql -d ${*} -c "ALTER USER ${*} SET search_path TO ${SCHEMA};"
	@$(RUN_POSTGRES) psql -d ${*} -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA ${SCHEMA} TO ${*};"
	@echo 'DB_URL="pgsql://${*}:${*}@127.0.0.1:5432/${*}"'
db-create-mysql: ## create mysql database
	@$(RUN_ADMIN) doctrine:database:drop --if-exists --force
	@$(RUN_ADMIN) doctrine:database:create
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction

## —— Demo —————————————————————————————————————————————————————————————————————————————————————————————————————————————
demo: ## make new demo
	@$(MAKE) -s cache-clear
	@$(MAKE) -s db-drop/"demo"
	@$(MAKE) -s db-create/"demo" SCHEMA="schema_demo_adm"
	@$(MAKE) -s db-migrate
	@$(MAKE) -s assets-install
	@$(MAKE) -s assets-build
	@$(RUN_ADMIN) emsco:user:create demo demo@example.com demo --super-admin
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_API
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_FORM_CRM
	@$(MAKE) -s demo-npm/"install"
	@$(MAKE) -s demo-npm/"run build"
	@$(RUN_ADMIN) assets:install --symlink
	@$(RUN_WEB) assets:install --symlink
	@ln -sf ${PWD}/demo/dist ${PWD}/elasticms-web/public/bundles/demo
	@ln -sf ${PWD}/demo/dist ${PWD}/elasticms-admin/public/bundles/demo
	@$(RUN_ADMIN) ems:admin:login --username=demo --password=demo
	@$(RUN_ADMIN) ems:admin:restore --configs-folder=./demo/configs/admin --configs --force
	@$(RUN_ADMIN) ems:contenttype:activate --all --force --no-debug
	@$(RUN_ADMIN) ems:environment:rebuild --all --no-debug
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_preview preview
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_preview default
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_live live
	@$(RUN_ADMIN) ems:managed-alias:add-environment ma_live default
	@$(RUN_ADMIN) emsco:user:add-group demo admins
	@$(RUN_ADMIN) emsch:local:login demo demo
	@$(RUN_ADMIN) emsch:local:push --force
	@$(RUN_ADMIN) emsch:local:upload --filename=./demo/skeleton/template/asset_hash.twig --as-style-set-assets
	@$(RUN_ADMIN) emsch:local:folder-upload ./demo/configs/admin/assets
	@$(RUN_ADMIN) ems:admin:restore --documents-folder=./demo/configs/document --documents --force
	@$(RUN_ADMIN) ems:environment:align preview live --force --no-debug
	@$(RUN_WEB) ems:admin:login --username=demo --password=demo
	@$(RUN_WEB) ems:admin:webhooks:register http://localhost:8882/_admin_webhook content.finalize content.delete environment.new_index.preview
demo-backup-configs: ## backup demo configs
	@$(RUN_WEB) c:c
	@$(RUN_WEB) ems:admin:login --username=demo --password=demo
	@$(RUN_WEB) ems:admin:backup --configs-folder=./demo/configs/admin --configs --export
demo-backup-documents: ## backup demo documents
	@$(RUN_ADMIN) ems:admin:backup --documents-folder=./demo/configs/document --documents --export
demo-npm/%: ## demo npm
	@$(RUN_DEMO_NPM) $*
demo-npm-watch: ## demo npm run watch
	@$(MAKE) -s demo-npm/"run watch"
