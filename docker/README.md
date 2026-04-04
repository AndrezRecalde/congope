# 🗄️ CONGOPE — Stack de Base de Datos

Stack de base de datos para la **Plataforma de Cooperación Internacional y Nacional del CONGOPE**.

## Componentes

| Servicio | Imagen | Versión | Puerto |
|----------|--------|---------|--------|
| PostgreSQL + PostGIS | `postgis/postgis` | 17-3.5 | 5432 |
| pgAdmin 4 | `dpage/pgadmin4` | latest | 5050 |

## Extensiones instaladas automáticamente

| Extensión | Propósito |
|-----------|-----------|
| `postgis` | Datos geoespaciales (módulo de mapa) |
| `postgis_topology` | Topología geoespacial |
| `uuid-ossp` | Generación de UUIDs (PKs) |
| `pgcrypto` | Funciones criptográficas |
| `unaccent` | Búsqueda de texto sin tildes |
| `pg_cron` | Tareas programadas automáticas |
| `pg_stat_statements` | Monitoreo de rendimiento |

---

## Inicio rápido

### 1. Clonar y configurar

```bash
# Copiar variables de entorno
cp .env.example .env

# Editar credenciales (opcional en desarrollo)
nano .env
```

### 2. Levantar los servicios

```bash
# Con Makefile (recomendado)
make up

# O directamente con Docker
docker compose up -d
```

### 3. Verificar que todo funciona

```bash
make status
```

Deberías ver ambos contenedores en estado `healthy`.

---

## Accesos

### PostgreSQL
```
Host:     localhost
Puerto:   5432
Base de datos: congope_db
Usuario:  congope_user
Password: Congope@2025!
```

### pgAdmin
```
URL:      http://localhost:5050
Email:    admin@congope.gob.ec
Password: Admin@2025!
```

El servidor **CONGOPE — PostgreSQL 17 (Local)** aparece pre-configurado
en pgAdmin. Solo haz clic en él y ya está conectado.

---

## Configurar variables en Laravel

En el archivo `.env` de tu proyecto Laravel:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=congope_db
DB_USERNAME=congope_user
DB_PASSWORD=Congope@2025!
```

---

## Flujo de trabajo con Laravel

```bash
# 1. Levantar la base de datos
make up

# 2. Ejecutar migraciones de Laravel
php artisan migrate

# 3. Cargar datos semilla
php artisan db:seed

# 4. Configurar pg_cron (solo una vez, post-migración)
make cron-setup

# 5. Verificar tablas creadas
make tables
```

---

## Comandos disponibles

```bash
make help           # Ver todos los comandos disponibles
make up             # Levantar servicios
make down           # Detener servicios
make restart        # Reiniciar servicios
make logs           # Ver logs en tiempo real
make status         # Estado de contenedores
make psql           # Abrir consola psql
make shell          # Shell bash en PostgreSQL
make extensions     # Ver extensiones instaladas
make postgis-check  # Verificar PostGIS
make cron-setup     # Configurar pg_cron (post-migración)
make cron-list      # Ver jobs programados
make backup         # Crear backup comprimido
make backup-schema  # Backup solo del esquema
make restore        # Restaurar backup
make tables         # Listar tablas
make sizes          # Ver tamaño de tablas
make clean          # ⚠️ Eliminar todo (irreversible)
```

---

## Estructura de archivos

```
congope-docker/
├── docker-compose.yml          # Definición de servicios
├── .env.example                # Variables de entorno (plantilla)
├── .env                        # Variables reales (NO commitear)
├── .gitignore
├── Makefile                    # Comandos útiles
├── README.md
├── postgres/
│   └── init/
│       ├── 01_extensions.sql           # Extensiones automáticas
│       ├── 02_roles.sql                # Roles y privilegios
│       ├── 03_funciones.sql            # Funciones de mantenimiento
│       └── 04_cron_jobs_post_migrate.sql  # Jobs pg_cron (manual)
└── pgadmin/
    ├── servers.json            # Servidor pre-configurado
    └── pgpass                  # Credenciales automáticas
```

---

## Notas importantes

**Primer arranque:** Los scripts de `postgres/init/` se ejecutan
**una sola vez** al crear el volumen. Si necesitas re-ejecutarlos,
usa `make clean` primero (⚠️ borra todos los datos).

**pg_cron:** El script `04_cron_jobs_post_migrate.sql` debe ejecutarse
**después** de que las tablas de Laravel existan, por eso es manual.

**Backups:** Se guardan en la carpeta `backups/` con timestamp.
Están en `.gitignore` para no subir datos al repositorio.
