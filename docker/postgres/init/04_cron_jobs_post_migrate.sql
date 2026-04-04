-- ══════════════════════════════════════════════════════════════════════════════
-- CONGOPE — Script post-migración
-- Ejecutar MANUALMENTE después de: php artisan migrate --seed
--
-- Comando para ejecutar:
--   docker exec -i congope_postgres psql -U congope_user -d congope_db \
--     -f /docker-entrypoint-initdb.d/04_cron_jobs_post_migrate.sql
--
-- O desde pgAdmin: abrir Query Tool y pegar el contenido
-- ══════════════════════════════════════════════════════════════════════════════

\echo '━━━ [04-POST] Configurando jobs de pg_cron ━━━'

-- ── Limpiar jobs existentes para evitar duplicados ────────────────────────────
SELECT cron.unschedule(jobid)
FROM cron.job
WHERE jobname LIKE 'congope_%';

-- ── JOB 1: Limpieza mensual de auditoría ─────────────────────────────────────
-- Ejecuta el primer día de cada mes a las 02:00 AM (hora Ecuador)
SELECT cron.schedule(
    'congope_limpiar_auditoria',
    '0 2 1 * *',
    'SELECT limpiar_auditoria_antigua()'
);

-- ── JOB 2: Recalcular calificaciones de buenas prácticas ─────────────────────
-- Cada domingo a las 03:00 AM (hora Ecuador)
SELECT cron.schedule(
    'congope_recalcular_calificaciones',
    '0 3 * * 0',
    'SELECT recalcular_calificaciones()'
);

-- ── JOB 3: VACUUM y ANALYZE programado ───────────────────────────────────────
-- Optimización de tablas más grandes, cada sábado a las 01:00 AM
SELECT cron.schedule(
    'congope_vacuum_analyze',
    '0 1 * * 6',
    $$
        VACUUM ANALYZE proyectos;
        VACUUM ANALYZE actores_cooperacion;
        VACUUM ANALYZE registros_auditoria;
        VACUUM ANALYZE documentos;
        VACUUM ANALYZE buenas_practicas;
    $$
);

-- ── JOB 4: Backup lógico de seguridad ────────────────────────────────────────
-- Genera un dump diario a las 23:30 (hora Ecuador) dentro del contenedor
-- El volumen postgres_data persiste los backups
SELECT cron.schedule(
    'congope_backup_diario',
    '30 23 * * *',
    $$
        SELECT pg_start_backup('congope_daily_' || to_char(now(), 'YYYYMMDD'));
        SELECT pg_stop_backup();
    $$
);

-- ── Verificar jobs registrados ────────────────────────────────────────────────
\echo '━━━ Jobs registrados en pg_cron: ━━━'
SELECT
    jobid,
    jobname,
    schedule,
    command,
    active
FROM cron.job
WHERE jobname LIKE 'congope_%'
ORDER BY jobid;

\echo '━━━ [04-POST] pg_cron configurado correctamente ━━━'
