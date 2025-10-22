.PHONY: help build up down restart logs shell db-shell migrate seed fresh test cache-clear composer-install npm-install wait-db swagger api-docs

help: ## Mostra esta ajuda
	@echo "Comandos disponíveis:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Constrói os containers Docker
	docker compose build

up: ## Inicia os containers
	docker compose up -d

down: ## Para os containers
	docker compose down

restart: down up ## Reinicia os containers

logs: ## Mostra os logs dos containers
	docker compose logs -f

shell: ## Acessa o shell do container da aplicação
	docker compose exec app bash

db-shell: ## Acessa o shell do PostgreSQL
	docker compose exec db psql -U laravel -d api_estoque

wait-db: ## Aguarda o PostgreSQL ficar pronto
	@echo "Aguardando PostgreSQL..."
	@docker compose exec -T db sh -c 'until pg_isready -U laravel -d api_estoque; do sleep 1; done'
	@echo "PostgreSQL está pronto!"

migrate: ## Executa as migrations
	docker compose exec app php artisan migrate

migrate-fresh: ## Recria o banco de dados e executa migrations
	docker compose exec app php artisan migrate:fresh

seed: ## Executa os seeders
	docker compose exec app php artisan db:seed

fresh: migrate-fresh seed ## Recria o banco e popula com seeders

test: ## Executa os testes
	docker compose exec app php artisan test

cache-clear: ## Limpa todos os caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

composer-install: ## Instala dependências do Composer
	docker compose exec app composer install

npm-install: ## Instala dependências do NPM
	docker compose exec app npm install

npm-build: ## Compila os assets
	docker compose exec app npm run build

setup: ## Configuração inicial completa
	cp .env.example .env
	rm -f composer.lock
	docker compose down -v
	docker compose up -d --build
	@$(MAKE) wait-db
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate:fresh --seed
	@echo "✅ Setup concluído!"
	@echo ""
	@echo "🌐 API disponível em: http://localhost:8000/api/v1"
	@echo "📊 PgAdmin disponível em: http://localhost:5050 (admin@admin.com / admin)"
	@echo ""
	@echo "📖 Execute 'make swagger' para instalar documentação Swagger"

permissions: ## Ajusta permissões dos diretórios
	docker compose exec app chown -R laravel:www-data /var/www
	docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

swagger: ## Instala e configura Swagger
	docker compose exec app composer require darkaonline/l5-swagger
	docker compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
	docker compose exec app php artisan l5-swagger:generate

api-docs: ## Gera documentação da API
	docker compose exec app php artisan l5-swagger:generate
	@echo "Documentação disponível em http://localhost:8000/api/documentation"
