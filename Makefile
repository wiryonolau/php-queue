# HELP
# This will output the help for each task
.PHONY: help

help: ## This help.
    @awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help

THIS_FILE := $(lastword $(MAKEFILE_LIST))
PHP_VERSION ?= "8.1"

%:
	@echo ""
all:
	@echo ""
build:
	@if [ "$$(docker images -q php:$(PHP_VERSION)-cli-ext 2>/dev/null)" = "" ]; then \
		cd $$(pwd)/docker/php-cli-ext && docker build --build-arg PHP_VERSION=$(PHP_VERSION) -t php:$(PHP_VERSION)-cli-ext .; \
	fi
run:
	$(MAKE) build
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
		-w /srv/$$(basename "`pwd`") \
		--user "$$(id -u):$$(id -g)" \
        --name $$(basename "`pwd`")_cli \
    php:$(PHP_VERSION)-cli-ext $(filter-out $@,$(MAKECMDGOALS))
unittest:
	$(MAKE) build
	docker stop $$(basename "`pwd`")_cli || true
	docker run --rm -d \
		-p 5671:5671 \
		-p 5672:5672 \
      	-v $$(pwd)/etc/rabbitmq/cert:/etc/rabbitmq/cert:ro \
      	-v $$(pwd)/etc/rabbitmq/rabbitmq.conf:/etc/rabbitmq/rabbitmq.conf:ro \
      	-v $$(pwd)/etc/rabbitmq/definitions.json:/etc/rabbitmq/definitions.json:ro \
	    --name $$(basename "`pwd`")_rabbitmq \
	rabbitmq:3.12-alpine || true
	sleep 3
	@while [ "$$(docker exec -it $$(basename "`pwd`")_rabbitmq rabbitmq-diagnostics -q check_port_connectivity > /dev/null && echo 0 || echo 1 )" -eq "1" ]; do \
       	echo "Awaiting port 5672 to be ready" ; \
       	sleep 1; \
    done
	docker run --rm -it \
		-e TEST_TLS=1 \
		-e TEST_SERVICE_QUEUE=1 \
		-e TEST_FANOUT=1 \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
		-w /srv/$$(basename "`pwd`") \
		--user "$$(id -u):$$(id -g)" \
        --name $$(basename "`pwd`")_cli \
		--network host \
    php:$(PHP_VERSION)-cli-ext vendor/bin/phpunit --verbose --debug tests
clean:
	docker stop $$(basename "`pwd`")_cli || true
	docker stop $$(basename "`pwd`")_rabbitmq || true
composer-install:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
        -w /srv/$$(basename "`pwd`") \
        -e COMPOSER_HOME="/srv/$$(basename "`pwd`")/.composer" \
        --user $$(id -u):$$(id -g) \
    composer composer install --no-plugins --no-scripts --prefer-dist -v
composer-update:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
        -w /srv/$$(basename "`pwd`") \
        -e COMPOSER_HOME="/srv/$$(basename "`pwd`")/.composer" \
        --user $$(id -u):$$(id -g) \
    composer composer update --no-plugins --no-scripts --prefer-dist -v
composer:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
        -w /srv/$$(basename "`pwd`") \
        -e COMPOSER_HOME="/srv/$$(basename "`pwd`")/.composer" \
        --user $$(id -u):$$(id -g) \
    composer composer $(filter-out $@,$(MAKECMDGOALS))
