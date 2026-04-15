# ============================================================
# SecuriForm — Comandos de desarrollo con Docker
# Uso: make [comando]
# ============================================================

.PHONY: help init up down restart build shell db-shell redis-shell \
        migrate migrate-fresh seed tinker logs logs-app logs-queue \
        queue queue-restart queue-failed queue-retry queue-flush \
        test filament-user npm-dev npm-build clean cache-clear \
        release diagrams

# Colores
YELLOW=\033[1;33m
GREEN=\033[1;32m
CYAN=\033[1;36m
NC=\033[0m

help: ## Mostrar esta ayuda
	@echo ""
	@echo "$(CYAN)SecuriForm — Comandos disponibles$(NC)"
	@echo "=================================="
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-18s$(NC) %s\n", $$1, $$2}'
	@echo ""

# ── INICIALIZACION ────────────────────────────────────────────

init: ## Primera vez: crea proyecto Laravel + instala Filament
	@echo "$(YELLOW)>> Construyendo imagen Docker...$(NC)"
	docker compose build
	@echo "$(YELLOW)>> Levantando servicios (DB, Redis)...$(NC)"
	docker compose up -d db redis
	@echo "$(YELLOW)>> Esperando a que la BD este lista...$(NC)"
	docker compose exec db bash -c 'while ! healthcheck.sh --connect 2>/dev/null; do sleep 1; done'
	@echo "$(YELLOW)>> Creando proyecto Laravel...$(NC)"
	docker compose run --rm --no-deps app bash -c '\
		if [ ! -f artisan ]; then \
			composer create-project laravel/laravel /tmp/laravel --prefer-dist --no-interaction && \
			cp -rn /tmp/laravel/. /var/www/html/ && \
			rm -rf /tmp/laravel; \
		else \
			echo "Laravel ya existe, saltando creacion..."; \
		fi'
	@echo "$(YELLOW)>> Instalando Filament PHP...$(NC)"
	docker compose run --rm --no-deps app bash -c '\
		composer require filament/filament --no-interaction && \
		php artisan filament:install --panels --no-interaction'
	@echo "$(YELLOW)>> Instalando dependencias adicionales...$(NC)"
	docker compose run --rm --no-deps app bash -c '\
		composer require \
			phpmailer/phpmailer \
			mpdf/mpdf \
			phpoffice/phpspreadsheet \
			spatie/laravel-permission \
			stancl/tenancy \
			--no-interaction'
	@echo "$(YELLOW)>> Levantando todos los servicios...$(NC)"
	docker compose up -d
	@echo "$(YELLOW)>> Esperando a que la app arranque...$(NC)"
	sleep 5
	@echo "$(YELLOW)>> Ejecutando migraciones...$(NC)"
	docker compose exec app php artisan migrate --force
	@echo "$(YELLOW)>> Instalando assets de Node...$(NC)"
	docker compose exec app npm install
	@echo ""
	@echo "$(GREEN)=============================================$(NC)"
	@echo "$(GREEN) SecuriForm instalado correctamente!$(NC)"
	@echo "$(GREEN)=============================================$(NC)"
	@echo ""
	@echo "  App:        $(CYAN)http://localhost:8080$(NC)"
	@echo "  Admin:      $(CYAN)http://localhost:8080/admin$(NC)"
	@echo "  phpMyAdmin:  $(CYAN)http://localhost:8081$(NC)"
	@echo "  MailHog:     $(CYAN)http://localhost:8025$(NC)"
	@echo ""
	@echo "  Crear usuario admin:"
	@echo "    $(YELLOW)make filament-user$(NC)"
	@echo ""

# ── DOCKER ────────────────────────────────────────────────────

up: ## Levantar todos los servicios
	docker compose up -d
	@echo "$(GREEN)App: http://localhost:8080 | Admin: http://localhost:8080/admin$(NC)"

down: ## Parar todos los servicios
	docker compose down

restart: ## Reiniciar todos los servicios
	docker compose restart

build: ## Reconstruir la imagen Docker
	docker compose build --no-cache

logs: ## Ver logs de todos los servicios (Ctrl+C para salir)
	docker compose logs -f

logs-app: ## Ver logs solo de la app
	docker compose logs -f app

# ── SHELL / ACCESO ────────────────────────────────────────────

shell: ## Entrar al contenedor de la app (bash)
	docker compose exec app bash

db-shell: ## Entrar a la consola MySQL
	docker compose exec db mysql -u securiform -psecuriform_dev_pass securiform

redis-shell: ## Entrar a la consola Redis
	docker compose exec redis redis-cli

tinker: ## Abrir Laravel Tinker (REPL)
	docker compose exec app php artisan tinker

# ── LARAVEL ───────────────────────────────────────────────────

migrate: ## Ejecutar migraciones pendientes
	docker compose exec app php artisan migrate

migrate-fresh: ## Borrar BD y re-ejecutar todas las migraciones + seeders
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Ejecutar seeders
	docker compose exec app php artisan db:seed

test: ## Ejecutar tests (PHPUnit/Pest)
	docker compose exec app php artisan test

queue: ## Procesar cola de trabajos (foreground)
	docker compose exec app php artisan queue:work redis --tries=3

queue-restart: ## Reiniciar el worker de cola (contenedor dedicado)
	docker compose restart queue

queue-failed: ## Ver jobs fallidos
	docker compose exec app php artisan queue:failed

queue-retry: ## Reintentar todos los jobs fallidos
	docker compose exec app php artisan queue:retry all

queue-flush: ## Eliminar todos los jobs fallidos
	docker compose exec app php artisan queue:flush

logs-queue: ## Ver logs del queue worker
	docker compose logs -f queue

# ── FILAMENT ──────────────────────────────────────────────────

filament-user: ## Crear usuario administrador para Filament
	docker compose exec app php artisan make:filament-user

# ── ASSETS (Vite) ─────────────────────────────────────────────

npm-dev: ## Compilar assets en modo desarrollo (watch)
	docker compose exec app npm run dev

npm-build: ## Compilar assets para produccion
	docker compose exec app npm run build

# ── LIMPIEZA ──────────────────────────────────────────────────

clean: ## Borrar volumenes (BD, vendor, node_modules) y empezar de cero
	docker compose down -v
	@echo "$(YELLOW)Volumenes eliminados. Ejecuta 'make init' para reinstalar.$(NC)"

cache-clear: ## Limpiar todos los caches de Laravel
	docker compose exec app php artisan optimize:clear

# ── RELEASE ──────────────────────────────────────────────────

release: ## Crear tag de release (uso: make release V=0.2.0)
	@if [ -z "$(V)" ]; then echo "$(YELLOW)Uso: make release V=x.y.z$(NC)"; exit 1; fi
	@echo "$(YELLOW)>> Creando tag v$(V)...$(NC)"
	git tag -a v$(V) -m "Release v$(V)"
	git push origin v$(V)
	@echo "$(GREEN)Tag v$(V) creado y pusheado.$(NC)"

# ── DOCUMENTACION ────────────────────────────────────────────

diagrams: ## Renderizar diagramas PlantUML a PNG
	docker run --rm -v "$(PWD)/docs/diagrams:/data" plantuml/plantuml /data/**/*.puml
	@echo "$(GREEN)Diagramas renderizados en docs/diagrams/$(NC)"
