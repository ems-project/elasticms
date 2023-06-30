#!/usr/bin/make -f

include .env
export $(grep -v '^#' .env | xargs)
include .env.local
export $(grep -v '^#' .env.local | xargs)

SYMFONY		= symfony

.DEFAULT_GOAL := help
.PHONY: help

help: # Show help for each of the Makefile recipes.
	@echo "EMS WEB"
	@echo "---------------------------"
	@echo "ENV:         ${APP_ENV}"
	@echo "DB:          ${DB_URL}"
	@echo "DOCKER_USER: ${DOCKER_USER}"
	@echo "URL:         http://localhost:8882"
	@echo "---------------------------"
	@echo "Usage: make [target]"
	@echo "Targets:"
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Symfony Server ———————————————————————————————————————————————————————————————————————————————————————————————————
server-start: ## start symfony server (8882)
	@$(SYMFONY) server:start -d --port=8882
	@echo "Started http://localhost:8882"
server-stop: ## stop symfony server
	@$(SYMFONY) server:stop
server-log: ## logs symfony server
	@$(SYMFONY) server:log