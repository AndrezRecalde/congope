-- ══════════════════════════════════════════════════════════════════════════════
-- CONGOPE — Script de inicialización 02
-- Configuración de roles de base de datos y privilegios
-- ══════════════════════════════════════════════════════════════════════════════

\echo '━━━ [02] Configurando roles y privilegios de BD ━━━'

-- ── Rol de solo lectura (para reportes y visualizadores) ─────────────────────
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'congope_readonly') THEN
        CREATE ROLE congope_readonly;
        RAISE NOTICE 'Rol congope_readonly creado';
    ELSE
        RAISE NOTICE 'Rol congope_readonly ya existe';
    END IF;
END
$$;

-- ── Privilegios para el usuario principal ─────────────────────────────────────
GRANT ALL PRIVILEGES ON DATABASE congope_db TO congope_user;
GRANT ALL ON SCHEMA public TO congope_user;

-- ── Configuración de búsqueda de schema ───────────────────────────────────────
ALTER DATABASE congope_db SET search_path TO public;

-- ── Configuración de zona horaria de Ecuador ──────────────────────────────────
ALTER DATABASE congope_db SET timezone TO 'America/Guayaquil';

-- ── Configuración de idioma para búsquedas de texto ──────────────────────────
ALTER DATABASE congope_db SET default_text_search_config TO 'pg_catalog.spanish';

\echo '━━━ [02] Roles y privilegios configurados correctamente ━━━'
