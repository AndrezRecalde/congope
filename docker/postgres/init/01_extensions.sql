-- ══════════════════════════════════════════════════════════════════════════════
-- CONGOPE — Script de inicialización 01
-- Activar extensiones requeridas por el proyecto
-- Se ejecuta automáticamente al crear el contenedor por primera vez
-- ══════════════════════════════════════════════════════════════════════════════

\echo '━━━ [01] Activando extensiones PostgreSQL para CONGOPE ━━━'

-- ── Extensión geoespacial (requerida por el módulo de Mapa) ──────────────────
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;

-- ── UUID nativos de PostgreSQL ────────────────────────────────────────────────
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- ── Búsqueda de texto completo en español ────────────────────────────────────
CREATE EXTENSION IF NOT EXISTS unaccent;

-- ── Tareas programadas (alertas de vencimiento) ───────────────────────────────
CREATE EXTENSION IF NOT EXISTS pg_cron;

-- ── Estadísticas de rendimiento ───────────────────────────────────────────────
CREATE EXTENSION IF NOT EXISTS pg_stat_statements;

\echo '━━━ Verificando versiones instaladas ━━━'
SELECT
    name,
    default_version,
    installed_version,
    comment
FROM pg_available_extensions
WHERE name IN (
    'postgis',
    'postgis_topology',
    'uuid-ossp',
    'pgcrypto',
    'unaccent',
    'pg_cron',
    'pg_stat_statements'
)
ORDER BY name;

\echo '━━━ PostGIS version ━━━'
SELECT postgis_full_version();

\echo '━━━ [01] Extensiones activadas correctamente ━━━'
