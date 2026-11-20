.PHONY: help build up down ps logs shell console composer-install test health drill-db-down drill-db-up

DOCKER_COMPOSE := docker compose

help:
	@echo "BuildGuard Milestone 0 targets:"
	@echo "  make build            Build container images"
	@echo "  make up               Start all services in background"
	@echo "  make down             Stop and remove containers"
	@echo "  make ps               List container status"
	@echo "  make logs             Tail container logs"
	@echo "  make shell            Open a shell inside the php container"
	@echo "  make console          Run Symfony console (pass cmd=...)"
	@echo "  make composer-install Install PHP dependencies inside php container"
	@echo "  make test             Run PHPUnit tests inside php container"
	@echo "  make health           Call GET /health endpoint"
	@echo "  make drill-db-down    Stop MySQL container for failure drill"
	@echo "  make drill-db-up      Start MySQL container after failure drill"

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down

ps:
	$(DOCKER_COMPOSE) ps

logs:
	$(DOCKER_COMPOSE) logs -f --tail=50

shell:
	$(DOCKER_COMPOSE) exec php sh

console:
	$(DOCKER_COMPOSE) exec -T php php bin/console $(cmd)

composer-install:
	$(DOCKER_COMPOSE) exec -T php composer install --no-interaction

test:
	$(DOCKER_COMPOSE) exec -T php php bin/phpunit

health:
	curl -i http://localhost:8080/health

drill-db-down:
	$(DOCKER_COMPOSE) stop mysql

drill-db-up:
	$(DOCKER_COMPOSE) start mysql
