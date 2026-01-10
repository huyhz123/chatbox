.PHONY: help build up down restart logs shell composer artisan npm test migrate fresh seed

# Colors for output
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

help: ## Show this help message
	@echo '${GREEN}Available commands:${RESET}'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  ${YELLOW}%-15s${RESET} %s\n", $$1, $$2}'

build: ## Build Docker containers
	docker-compose build

up: ## Start all containers
	docker-compose up -d
	@echo "${GREEN}Application started! Access at http://localhost${RESET}"
	@echo "${YELLOW}Run 'make logs' to see container logs${RESET}"

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

shell: ## Access app container shell
	docker-compose exec app sh

mysql: ## Access MySQL shell
	docker-compose exec mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}

redis: ## Access Redis CLI
	docker-compose exec redis redis-cli

composer: ## Run composer install
	docker-compose exec app composer install

composer-update: ## Run composer update
	docker-compose exec app composer update

artisan: ## Run artisan command (use: make artisan cmd="migrate")
	docker-compose exec app php artisan $(cmd)

npm: ## Run npm install
	docker-compose exec app npm install

npm-build: ## Build frontend assets
	docker-compose exec app npm run build

npm-dev: ## Start Vite dev server
	docker-compose --profile development up -d node
	@echo "${GREEN}Vite dev server started at http://localhost:5173${RESET}"

test: ## Run tests
	docker-compose exec app php artisan test

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migrate database
	docker-compose exec app php artisan migrate:fresh

seed: ## Seed database
	docker-compose exec app php artisan db:seed

fresh: ## Fresh migrate and seed database
	docker-compose exec app php artisan migrate:fresh --seed

cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

cache: ## Cache config, routes, views
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

optimize: ## Optimize application
	docker-compose exec app php artisan optimize

storage-link: ## Create storage symbolic link
	docker-compose exec app php artisan storage:link

queue-work: ## Run queue worker
	docker-compose exec app php artisan queue:work

reverb-start: ## Start Reverb WebSocket server
	docker-compose up -d reverb

install: ## Full installation
	@echo "${YELLOW}Installing application...${RESET}"
	docker-compose build
	docker-compose up -d
	docker-compose exec app composer install
	docker-compose exec app cp .env.example .env
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --seed
	docker-compose exec app php artisan storage:link
	docker-compose exec app npm install
	docker-compose exec app npm run build
	@echo "${GREEN}Installation complete!${RESET}"
	@echo "${YELLOW}Update .env file with your configuration${RESET}"
	@echo "${GREEN}Access application at http://localhost${RESET}"

dev: ## Start development environment
	docker-compose --profile development up -d
	@echo "${GREEN}Development environment started!${RESET}"
	@echo "Application: http://localhost"
	@echo "Vite: http://localhost:5173"
	@echo "phpMyAdmin: http://localhost:8081"
	@echo "Redis Commander: http://localhost:8082"

prod: ## Start production environment
	docker-compose up -d
	@echo "${GREEN}Production environment started!${RESET}"

status: ## Show container status
	docker-compose ps

stats: ## Show container stats
	docker stats

clean: ## Remove containers, volumes, and images
	docker-compose down -v --rmi all
	@echo "${YELLOW}All containers, volumes, and images removed${RESET}"

backup-db: ## Backup database
	@mkdir -p backups
	docker-compose exec mysql mysqldump -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > backups/db_backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "${GREEN}Database backed up to backups/${RESET}"

restore-db: ## Restore database (use: make restore-db file=backup.sql)
	docker-compose exec -T mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} < $(file)
	@echo "${GREEN}Database restored from $(file)${RESET}"

permissions: ## Fix file permissions
	docker-compose exec app chown -R www-data:www-data /var/www/html
	docker-compose exec app chmod -R 755 /var/www/html/storage
	docker-compose exec app chmod -R 755 /var/www/html/bootstrap/cache
	@echo "${GREEN}Permissions fixed${RESET}"
