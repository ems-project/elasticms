#!/usr/bin/make -f

PWD							= $(shell pwd)
DOCKER_USER			?= $(shell id -u)
DOCKER_COMPOSE	= docker compose -f docker/docker-compose.yml

PORT_admin 			= 8881
PORT_web 				= 8882

RUN_ADMIN				= php ${PWD}/elasticms-admin/bin/console --no-debug
RUN_WEB					= php ${PWD}/elasticms-web/bin/console --no-debug
RUN_PSQL				= docker exec -i -u ${DOCKER_USER}:0 -e PGUSER=postgres -e PGPASSWORD=adminpg ems-mono-postgres psql
RUN_DEMO_NPM		= docker run -u ${DOCKER_USER} --rm -it -v ${PWD}/demo:/opt/src --workdir /opt/src elasticms/base-php:8.1-cli-dev npm

.DEFAULT_GOAL := help
.PHONY: help demo

help: # Show help for each of the Makefile recipes.
	@echo "EMS Monorepo"
	@echo "---------------------------"
	@echo "DOCKER_USER: ${DOCKER_USER}"
	@echo "ADMIN:       http://localhost:8881"
	@echo "WEB:         http://localhost:8882"
	@echo "KIBANA:      http://kibana.localhost"
	@echo "MINIO:       http://minio.localhost"
	@echo "MAIL:        http://mailhog.localhost"
	@echo "---------------------------"
	@echo ""
	@echo "Usage: make [target]"
	@echo "Targets:"
	@grep -E '(^\S*:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Mono —————————————————————————————————————————————————————————————————————————————————————————————————————————————
init: ## init mono repo (copy .env)
	@cp -fp ./docker/.env.dist ./docker/.env
	@cp -fp ./elasticms-admin/.env.dist ./elasticms-admin/.env
	@cp -fp ./elasticms-admin/.env.local.dist ./elasticms-admin/.env.local
	@cp -fp ./elasticms-web/.env.dist ./elasticms-web/.env
	@cp -fp ./elasticms-web/.env.local.dist ./elasticms-web/.env.local
start: ## start docker, admin server, web server
	@$(DOCKER_COMPOSE) up -d
	@$(MAKE) -s server-start/admin
	@$(MAKE) -s server-start/web
stop: ## stop docker, admin server, web server
	@$(MAKE) -s server-stop/admin
	@$(MAKE) -s server-stop/web
	@$(DOCKER_COMPOSE) down
cache-clear: ## cache clear
	@$(RUN_ADMIN) c:cl
	@$(RUN_WEB) c:cl
status: ## status
	@$(DOCKER_COMPOSE) ps

## —— Symfony server ———————————————————————————————————————————————————————————————————————————————————————————————————
server-start/%: ## server-start/(admin|web)
	symfony server:start --dir=elasticms-${*} -d --port=$(PORT_$(*))
server-stop/%:  ## server-stop/(admin|web)
	symfony server:stop --dir=elasticms-${*}
server-log/%:  ## server-log/(admin|web)
	symfony server:log --dir=elasticms-${*}

## —— Database —————————————————————————————————————————————————————————————————————————————————————————————————————————
db-migrate: ## run doctrine migrations
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction
db-load/%: ## make db-load/"db_example" DUMP=../../dumps.sql
	@$(RUN_PSQL) -U ${*} < ${DUMP}
db-drop/%: ## db-drop/"db_example"
	@$(RUN_PSQL) -c "DROP DATABASE IF EXISTS ${*};"
	@$(RUN_PSQL) -c "DROP USER IF EXISTS ${*};"
db-create/%: ## db-create/"db_example" SCHEMA="schema_example_adm"
	@$(RUN_PSQL) -c "CREATE USER ${*} WITH ENCRYPTED PASSWORD '${*}';"
	@$(RUN_PSQL) -c "CREATE DATABASE ${*} WITH OWNER ${*};"
	@$(RUN_PSQL) -c "GRANT ALL PRIVILEGES ON DATABASE ${*} TO ${*};"
	@$(RUN_PSQL) -d ${*} -c "ALTER SCHEMA public OWNER TO ${*};"
	@$(RUN_PSQL) -d ${*} -c "ALTER SCHEMA public RENAME TO ${SCHEMA}"
	@$(RUN_PSQL) -d ${*} -c "ALTER USER ${*} SET search_path TO ${SCHEMA};"
	@$(RUN_PSQL) -d ${*} -c "GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA ${SCHEMA} TO ${*};"
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
	@$(RUN_ADMIN) emsco:user:create demo demo@example.com demo --super-admin
	@$(RUN_ADMIN) emsco:user:promote demo ROLE_API
	@$(MAKE) -s demo-npm/"install"
	@$(MAKE) -s demo-npm/"run prod"
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
	@$(RUN_ADMIN) emsco:contenttype:switch-default-env media_file default
	@$(RUN_ADMIN) emsch:local:login demo demo
	@$(RUN_ADMIN) emsch:local:push --force
	@$(RUN_ADMIN) emsch:local:upload --filename=./demo/skeleton/template/asset_hash.twig
	@$(RUN_ADMIN) emsch:local:folder-upload ./demo/configs/admin/assets
	@$(RUN_ADMIN) ems:admin:restore --documents-folder=./demo/configs/document --documents --force
	@$(RUN_ADMIN) ems:environment:align preview live --force --no-debug
demo-backup-configs: ## backup demo configs
	@$(RUN_ADMIN) ems:admin:backup --configs-folder=./demo/configs/admin --configs --export
demo-backup-documents: ## backup demo documents
	@$(RUN_ADMIN) ems:admin:backup --documents-folder=./demo/configs/document --documents --export
demo-npm/%: ## demo npm
	@$(RUN_DEMO_NPM) $*
demo-npm-watch: ## demo npm run watch
	@$(MAKE) -s demo-npm/"run watch"
