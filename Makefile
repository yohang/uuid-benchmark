DOCKER_COMPOSE = EXTERNAL_USER_ID=$(shell id -u) docker compose
HTTPS_PORT ?= 443
NUMBER_OF_ROOT_ENTITY = 1000000

.PHONY: ps build up first_run clean logs cli run reset cc deploy down assets/vendor test hadolint

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

run: .configured up ## Automatically Build & Run the project

clean: ## Stops and clean up the project (removes all data)
	@$(DOCKER_COMPOSE) down -v --remove-orphans
	@rm -rf .configured vendor assets/vendor var/cache var/log infra/frankenphp/tls public/assets public/build public/bundles public/coverage

ps: ## Show running containers
	@$(DOCKER_COMPOSE) ps

pull: ## Pulls remote images
	@$(DOCKER_COMPOSE) pull --ignore-pull-failures

build: ## Build the project
	@$(DOCKER_COMPOSE) build

up: ## Start the containers
	@$(DOCKER_COMPOSE) up -d --remove-orphans --wait php

down: ## Stop the containers
	@$(DOCKER_COMPOSE) down --remove-orphans

cli: ## Open a shell in the php container
	@$(DOCKER_COMPOSE) exec php bash

.configured:
	@test -f $@ || make first_run
	@touch $@

first_run: frankenphp/tls/cert.pem pull build vendor/ up assets/vendor reset

reset: ## Reset project fixtures
	@$(eval env ?= 'prod')
	$(DOCKER_COMPOSE) exec -eAPP_ENV=$(env) php composer reset

cc: ## Clear the Symfony cache
	@$(DOCKER_COMPOSE) exec php bin/console cache:clear

logs: ## Show logs, use "c=" to specify a container, default is php
	@$(eval c ?= 'php')
	@$(eval tail ?= 100)
	@$(DOCKER_COMPOSE) logs $(c) --tail=$(tail) --follow

vendor/:
	@$(DOCKER_COMPOSE) run --rm php composer install

frankenphp/tls/cert.pem:
	@mkdir -p infra/frankenphp/tls
	@mkcert -key-file infra/frankenphp/tls/key.pem -cert-file infra/frankenphp/tls/cert.pem localhost

test: env=test
test: reset ## Run PHPUnit test suite
	@$(DOCKER_COMPOSE) exec -eAPP_ENV=$(env) php ./vendor/bin/phpunit

test-coverage: env=test
test-coverage: reset ## Run PHPUnit test suite with HTML code coverage
	@$(DOCKER_COMPOSE) exec -eAPP_ENV=$(env) -eXDEBUG_MODE=coverage php ./vendor/bin/phpunit --coverage-html=public/coverage

infection:
	@$(DOCKER_COMPOSE) exec php php -dmemory_limit=-1 ./vendor/bin/infection --threads=4 --logger-html=public/infection

hadolint: ## Link Dockerfile
	@docker pull hadolint/hadolint
	@docker run --rm -i hadolint/hadolint hadolint < Dockerfile

cs: ## Fix code style
	@docker run --rm -v $(PWD):/app -w /app ghcr.io/php-cs-fixer/php-cs-fixer:3-php8.3 fix
	@docker compose exec -T php ./vendor/bin/twig-cs-fixer fix

psalm: ## Run static analysis
	@$(DOCKER_COMPOSE) exec php ./vendor/bin/psalm --no-diff

psalm_strict: ## Run static analysis (strict mode)
	@$(DOCKER_COMPOSE) exec php ./vendor/bin/psalm --show-info=true --no-diff

ID_TYPES = int uuid_v1 uuid_v4 uuid_v6 uuid_v7
benchmark: ## Reset project fixtures
	@$(eval env ?= 'prod')
	for ID_TYPE in $(ID_TYPES); do \
		$(DOCKER_COMPOSE) exec \
			-eCOMPOSER_PROCESS_TIMEOUT=3600 \
			-eID_TYPE=$$ID_TYPE \
			-eNUMBER_OF_ROOT_ENTITY=$(NUMBER_OF_ROOT_ENTITY) \
			-eAPP_ENV=$(env) \
				php composer benchmark; \
	done

test-workflow:
	gh act --artifact-server-path=$(HOME)/.local.share/act/
