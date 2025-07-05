# Executables (local)
DOCKER = docker
DOCKER_COMP = docker compose --env-file docker.env
DOCKER_COMP_EXEC = docker compose --env-file docker.env exec
PHP_EXEC = $(DOCKER_COMP_EXEC) webserver

DOCKER_CONTAINERS = $(shell docker ps -q -f "name=skillify-dev*")
MYSQL_USER?=skillify## Do `make <target> ... MYSQL_USER=<something> ...` to define the database user
MYSQL_PASSWORD?=skillify## Do `make <target> ... MYSQL_PASSWORD=<something> ...` to define the database password

# Executables
PHP      = $(PHP_EXEC) php
COMPOSER = $(PHP_EXEC) php -d memory_limit=-1 /usr/bin/composer
SYMFONY  = $(PHP_EXEC) php -d memory_limit=-1 bin/console
RM       = sudo rm -rf

define wait
until $3 > /dev/null ; do \
	>&2 echo "Waiting for $1 to be ready..."; sleep $2; \
done
endef

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

.SILENT: clean/docker clean stop check-db-container php db xdebug cc opc empty-var cache-clear

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
init: stop down up check-db-container vendor cc db-init ## Build and start the containers

clean: stop clean/docker
	git fetch --prune
	git fetch --tags --prune --prune-tags -f
	$(RM) ./vendor ./var/cache ./var/log

clean/docker: ## Delete all docker images used by skillify (and stop according running container)
	for image in $$($(DOCKER) images -q --filter reference="skillify-dev*:*" --format '{{.ID}}'); do \
		for container in $$($(DOCKER) ps -a -q --filter ancestor=$$image); do \
			$(DOCKER) rm -f $$container > /dev/null; \
		done; \
		$(DOCKER) rmi -f $$image; \
	done;

reinstall: clean init

cc: empty-var cache-clear opc

empty-var: ## empty symfony var directory
	$(PHP_EXEC) rm -rf var
	$(PHP_EXEC) mkdir -p var/log var/cache public/uploads > /dev/null
	$(PHP_EXEC) chmod -R 777 var public/uploads

opc: ## Reload php-fpm processes to clear opcache cache
	$(PHP_EXEC) service apache2 reload
	$(PHP_EXEC) chmod -R 777 var

up: ## Start the docker's containers
	@$(DOCKER_COMP) up --detach --build

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

stop: ## Docker stop all containers
ifneq ($(strip $(DOCKER_CONTAINERS)),)
	$(DOCKER) stop $(DOCKER_CONTAINERS)
endif

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

php: ## Enter webserver container as root
	@echo "Entering Symfony container..."
	$(DOCKER_COMP_EXEC) webserver /bin/bash

db: ## Enter database container as root
	@echo "Entering MySQL container..."
	$(DOCKER_COMP_EXEC) database /bin/bash

xdebug: ## Enable/Disable Xdebug in webserver container
	$(eval action:=$(filter-out $@,$(MAKECMDGOALS)))
	@if [ "$(action)" = "on" ]; then\
		echo "Enabling Xdebug in webserver container...";\
		$(PHP_EXEC) /usr/local/bin/xdebug.sh enable;\
		$(PHP_EXEC) service apache2 reload;\
	fi
	@if [ "$(action)" = "off" ]; then\
		echo "Disabling Xdebug in webserver container...";\
		$(PHP_EXEC) /usr/local/bin/xdebug.sh  disable;\
		$(PHP_EXEC) service apache2 reload;\
	fi
%:
	@:

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-progress --no-interaction
vendor: composer

# —— Database 🗄 ——————————————————————————————————————————————————————————————
check-db-container: ## Check db container is up
	$(call wait,MySQL,1,$(DOCKER_COMP_EXEC) database mysql -u $(MYSQL_USER) --password=$(MYSQL_PASSWORD) -e "SHOW DATABASES;" --silent | grep "information_schema")

db-init: db-clear-meta db-migrate db-fixtures ## db-migrate db-fixtures ## install database schema and run doctrine fixtures

db-reset: db-clear-meta db-migrate ## check database schema

db-migrate: ## Execute doctrine migrations
	$(SYMFONY) doctrine:migrations:migrate --no-interaction -vv

db-clear-meta: ## Clear doctrine metadata cache
	$(SYMFONY) doctrine:cache:clear-metadata

db-fixtures:
	$(SYMFONY) doctrine:fixtures:load --no-interaction -vv

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cache-clear: ## Clear the cache
cache-clear: c=cache:clear
cache-clear: sf

stan:
	@FILES=$$(git diff --name-only --diff-filter=ACMR HEAD | grep -e '\.php$$' | xargs); \
	if [ -n "$$FILES" ]; then \
		echo "Running PHPStan on: $$FILES"; \
		$(PHP) vendor/bin/phpstan analyse $$FILES || true; \
	else \
		echo "No PHP files changed."; \
	fi

cs-fix:
	@FILES=$$(git diff --name-only --diff-filter=ACMR HEAD | grep -e '\.php$$' | xargs); \
	if [ -n "$$FILES" ]; then \
		echo "Running PHPStan on: $$FILES"; \
		$(PHP) vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --using-cache=no -v $$FILES || true; \
	else \
		echo "No PHP files changed."; \
	fi