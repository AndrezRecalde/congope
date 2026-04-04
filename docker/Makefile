# ══════════════════════════════════════════════════════════════════════════════
# CONGOPE — Makefile de comandos útiles
# Uso: make <comando>
# ══════════════════════════════════════════════════════════════════════════════

.PHONY: up down restart logs shell psql backup restore status clean help

# Colores para output
GREEN  = \033[0;32m
YELLOW = \033[1;33m
RED    = \033[0;31m
NC     = \033[0m

help: ## Muestra esta ayuda
	@echo ""
	@echo "$(GREEN)CONGOPE — Comandos de Base de Datos$(NC)"
	@echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ""

up: ## Levantar todos los servicios
	@echo "$(GREEN)Levantando servicios CONGOPE...$(NC)"
	@cp -n .env.example .env 2>/dev/null || true
	docker compose up -d
	@echo "$(GREEN)✓ PostgreSQL disponible en: localhost:5432$(NC)"
	@echo "$(GREEN)✓ pgAdmin disponible en:    http://localhost:5050$(NC)"

down: ## Detener todos los servicios
	@echo "$(YELLOW)Deteniendo servicios CONGOPE...$(NC)"
	docker compose down

restart: ## Reiniciar todos los servicios
	docker compose restart

restart-db: ## Reiniciar solo PostgreSQL
	docker compose restart postgres

logs: ## Ver logs en tiempo real de todos los servicios
	docker compose logs -f

logs-db: ## Ver logs solo de PostgreSQL
	docker compose logs -f postgres

logs-pgadmin: ## Ver logs solo de pgAdmin
	docker compose logs -f pgadmin

status: ## Ver estado de los contenedores
	@echo ""
	@echo "$(GREEN)Estado de servicios CONGOPE:$(NC)"
	@docker compose ps
	@echo ""

shell: ## Abrir shell bash en el contenedor de PostgreSQL
	docker exec -it congope_postgres bash

psql: ## Abrir consola psql directamente
	docker exec -it congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db}

extensions: ## Verificar extensiones instaladas
	docker exec -it congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		-c "SELECT name, installed_version FROM pg_available_extensions WHERE installed_version IS NOT NULL ORDER BY name;"

postgis-check: ## Verificar instalación de PostGIS
	docker exec -it congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		-c "SELECT postgis_full_version();"

cron-setup: ## Ejecutar script de pg_cron (post-migración)
	@echo "$(YELLOW)Configurando jobs de pg_cron...$(NC)"
	docker exec -i congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		-f /docker-entrypoint-initdb.d/04_cron_jobs_post_migrate.sql
	@echo "$(GREEN)✓ pg_cron configurado$(NC)"

cron-list: ## Ver jobs programados en pg_cron
	docker exec -it congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		-c "SELECT jobid, jobname, schedule, active FROM cron.job ORDER BY jobid;"

backup: ## Crear backup completo de la base de datos
	@mkdir -p backups
	@FILENAME="backups/congope_backup_$$(date +%Y%m%d_%H%M%S).sql.gz"; \
	echo "$(GREEN)Creando backup: $$FILENAME$(NC)"; \
	docker exec congope_postgres pg_dump \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		--no-password \
		--format=plain \
		--verbose \
		| gzip > $$FILENAME; \
	echo "$(GREEN)✓ Backup creado: $$FILENAME$(NC)"

backup-schema: ## Crear backup solo del esquema (sin datos)
	@mkdir -p backups
	@FILENAME="backups/congope_schema_$$(date +%Y%m%d_%H%M%S).sql"; \
	echo "$(GREEN)Creando backup de esquema: $$FILENAME$(NC)"; \
	docker exec congope_postgres pg_dump \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		--schema-only \
		> $$FILENAME; \
	echo "$(GREEN)✓ Schema exportado: $$FILENAME$(NC)"

restore: ## Restaurar backup (uso: make restore FILE=backups/archivo.sql.gz)
	@if [ -z "$(FILE)" ]; then \
		echo "$(RED)ERROR: Especifica el archivo. Uso: make restore FILE=backups/archivo.sql.gz$(NC)"; \
		exit 1; \
	fi
	@echo "$(YELLOW)⚠️  Restaurando desde: $(FILE)$(NC)"
	@echo "$(YELLOW)   Esto sobreescribirá la base de datos actual$(NC)"
	@read -p "¿Continuar? (s/N): " confirm; \
	if [ "$$confirm" = "s" ] || [ "$$confirm" = "S" ]; then \
		gunzip -c $(FILE) | docker exec -i congope_postgres psql \
			-U $${POSTGRES_USER:-congope_user} \
			-d $${POSTGRES_DB:-congope_db}; \
		echo "$(GREEN)✓ Restauración completada$(NC)"; \
	else \
		echo "$(YELLOW)Operación cancelada$(NC)"; \
	fi

tables: ## Listar todas las tablas de la BD
	docker exec -it congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		-c "\dt public.*" \
		-c "SELECT COUNT(*) as total_tablas FROM information_schema.tables WHERE table_schema = 'public';"

sizes: ## Ver tamaño de tablas
	docker exec -it congope_postgres psql \
		-U $${POSTGRES_USER:-congope_user} \
		-d $${POSTGRES_DB:-congope_db} \
		-c "SELECT table_name, pg_size_pretty(pg_total_relation_size(quote_ident(table_name))) AS size FROM information_schema.tables WHERE table_schema = 'public' ORDER BY pg_total_relation_size(quote_ident(table_name)) DESC;"

clean: ## ⚠️  ELIMINAR TODO (contenedores + volúmenes con datos)
	@echo "$(RED)⚠️  ADVERTENCIA: Esto eliminará TODOS los datos persistentes$(NC)"
	@read -p "¿Estás seguro? Escribe 'ELIMINAR': " confirm; \
	if [ "$$confirm" = "ELIMINAR" ]; then \
		docker compose down -v --remove-orphans; \
		echo "$(GREEN)✓ Todo eliminado$(NC)"; \
	else \
		echo "$(YELLOW)Operación cancelada$(NC)"; \
	fi

.env: ## Crear .env desde .env.example si no existe
	@cp -n .env.example .env
	@echo "$(GREEN)✓ Archivo .env creado. Revisa las credenciales.$(NC)"
