-- ══════════════════════════════════════════════════════════════════════════════
-- CONGOPE — Script de inicialización 03
-- Configuración de tareas programadas con pg_cron
-- Estas tareas complementan el scheduler de Laravel
-- ══════════════════════════════════════════════════════════════════════════════

\echo '━━━ [03] Configurando tareas programadas pg_cron ━━━'

-- ── Asegurar que pg_cron puede acceder a la BD ────────────────────────────────
-- pg_cron requiere que la BD esté configurada en postgresql.conf
-- La imagen postgis/postgis ya lo incluye, solo verificamos

-- ── Función helper para limpiar registros de auditoría antiguos ───────────────
-- Esta función es llamada por el cron job mensual
CREATE OR REPLACE FUNCTION limpiar_auditoria_antigua()
RETURNS void AS $$
BEGIN
    DELETE FROM registros_auditoria
    WHERE created_at < NOW() - INTERVAL '2 years';

    RAISE NOTICE 'Auditoría limpiada: registros anteriores a 2 años eliminados';
END;
$$ LANGUAGE plpgsql;

-- ── Función para actualizar calificaciones de buenas prácticas ────────────────
-- Recalcula el promedio en caso de inconsistencias
CREATE OR REPLACE FUNCTION recalcular_calificaciones()
RETURNS void AS $$
BEGIN
    UPDATE buenas_practicas bp
    SET calificacion_promedio = COALESCE((
        SELECT AVG(puntuacion)::DECIMAL(3,2)
        FROM valoracion_practica vp
        WHERE vp.practica_id = bp.id
    ), 0.00)
    WHERE EXISTS (
        SELECT 1 FROM valoracion_practica
        WHERE practica_id = bp.id
    );

    RAISE NOTICE 'Calificaciones de buenas prácticas recalculadas';
END;
$$ LANGUAGE plpgsql;

-- ══════════════════════════════════════════════════════════════════════════════
-- NOTA IMPORTANTE:
-- Los jobs de pg_cron se configuran DESPUÉS de que las tablas existan,
-- es decir después de ejecutar las migraciones de Laravel.
-- El script 04_cron_jobs_post_migrate.sql debe ejecutarse manualmente
-- una vez que php artisan migrate haya corrido exitosamente.
-- ══════════════════════════════════════════════════════════════════════════════

\echo '━━━ [03] Funciones de mantenimiento creadas ━━━'
\echo '━━━ NOTA: Ejecutar 04_cron_jobs_post_migrate.sql después de las migraciones ━━━'
