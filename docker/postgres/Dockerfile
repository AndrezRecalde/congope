# ══════════════════════════════════════════════════════════════════════════════
# CONGOPE — Imagen personalizada PostgreSQL 17 + PostGIS 3.5 + pg_cron
# Extiende la imagen oficial postgis/postgis añadiendo pg_cron
# ══════════════════════════════════════════════════════════════════════════════

FROM postgis/postgis:17-3.5

LABEL maintainer="CONGOPE"
LABEL description="PostgreSQL 17 + PostGIS 3.5 + pg_cron para Plataforma CONGOPE"

# Instalar pg_cron desde el repositorio oficial de PostgreSQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        postgresql-17-cron \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar pg_cron en postgresql.conf
# pg_cron requiere ser cargado como shared_preload_libraries
RUN echo "shared_preload_libraries = 'pg_cron'" >> /usr/share/postgresql/postgresql.conf.sample \
    && echo "cron.database_name = 'congope_db'" >> /usr/share/postgresql/postgresql.conf.sample
