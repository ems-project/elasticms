#!/usr/bin/make -f

DOCKER_USER 	?= $UID
DOCKER_COMPOSE	= docker compose -f docker/docker-compose.yml

PWD			= $(shell pwd)

DEMO_DIR	= ${PWD}/demo
RUN_PSQL	= docker exec -i -u ${DOCKER_USER}:0 -e PGUSER=postgres -e PGPASSWORD=adminpg ems-mono-postgres psql
RUN_ADMIN	= php ${PWD}/elasticms-admin/bin/console --no-debug
RUN_WEB		= php ${PWD}/elasticms-web/bin/console --no-debug

export DEMO_DIR
export RUN_PSQL
export RUN_ADMIN
export RUN_WEB

.DEFAULT_GOAL := help
.PHONY: help

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
	@cp -u ./docker/.env.dist ./docker/.env
	@cp ./elasticms-admin/.env.dist ./elasticms-admin/.env
	@cp -u ./elasticms-admin/.env.local.dist ./elasticms-admin/.env.local
	@cp ./elasticms-web/.env.dist ./elasticms-web/.env
	@cp -u ./elasticms-web/.env.local.dist ./elasticms-web/.env.local
start: ## start docker, admin server, web server
	@$(MAKE) -s docker-up
	@$(MAKE) -s admin-server-start
	@$(MAKE) -s web-server-start
stop: ## stop docker, admin server, web server
	@$(MAKE) -s admin-server-stop
	@$(MAKE) -s web-server-stop
	@$(MAKE) -s docker-down
clear-cache:
	@$(RUN_ADMIN) c:cl
	@$(RUN_WEB) c:cl

## —— Demo —————————————————————————————————————————————————————————————————————————————————————————————————————————————
demo-init: ## init demo (new database PostgreSQL)
	@$(MAKE) clear-cache
	@$(MAKE) -C ./demo -s init
	@$(MAKE) demo-symlink-assets
demo-load: ## load demo data
	@$(RUN_ADMIN) c:cl
	@$(RUN_WEB) c:cl
	@$(MAKE) -C ./demo -s load
demo-local-status: ## local status
	@$(MAKE) -C ./demo -s web-local-status
demo-backup-configs: ## backup configs
	@$(MAKE) -C ./demo -s web-backup-configs
demo-backup-documents: ## backup documents
	@$(MAKE) -C ./demo -s web-backup-documents
demo-restore-configs: ## restore configs
	@$(MAKE) -C ./demo -s web-restore-configs
demo-restore-documents: ## restore documents
	@$(MAKE) -C ./demo -s web-restore-documents
demo-symlink-assets: ## symlink assets
	@ln -sf ${PWD}/demo/dist ${PWD}/elasticms-web/public/bundles/demo
	@ln -sf ${PWD}/demo/dist ${PWD}/elasticms-admin/public/bundles/demo

## —— Admin ————————————————————————————————————————————————————————————————————————————————————————————————————————————
admin-server-start: ## start symfony server (8881)
	@$(MAKE) -C ./elasticms-admin -s server-start
admin-server-stop: ## stop symfony server
	@$(MAKE) -C ./elasticms-admin -s server-stop
admin-server-log: ## log symfony server
	@$(MAKE) -C ./elasticms-admin -s server-log

## —— web ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
web-server-start: ## start symfony server (8882)
	@$(MAKE) -C ./elasticms-web -s server-start
web-server-stop: ## stop symfony server
	@$(MAKE) -C ./elasticms-web -s server-stop
web-server-log: ## log symfony server
	@$(MAKE) -C ./elasticms-web -s server-log

## —— Docker ———————————————————————————————————————————————————————————————————————————————————————————————————————————
docker-up: ## docker up
	@$(DOCKER_COMPOSE) up -d
docker-down: ## docker down
	@$(DOCKER_COMPOSE) down
docker-ps: ## docker ps
	@$(DOCKER_COMPOSE) ps

## —— Database —————————————————————————————————————————————————————————————————————————————————————————————————————————
db-migrate: ## run doctrine migrations
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction
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
db-load/%: ## make db-load/"db_example" DUMP=../../dumps.sql
	@$(RUN_PSQL) -U ${*} < ${DUMP}
db-create-mysql: ## create mysql database
	@$(RUN_ADMIN) doctrine:database:drop --if-exists --force
	@$(RUN_ADMIN) doctrine:database:create
	@$(RUN_ADMIN) doctrine:migrations:migrate --no-interaction