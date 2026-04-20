<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    /**
     * Directorio donde se guardan los backups.
     * Relativo a storage/app/
     */
    private const BACKUP_DIR = 'backups';

    /**
     * Extensión del archivo de backup.
     * pg_dump con formato plain text.
     */
    private const EXTENSION = '.sql';

    /**
     * Máximo de backups a conservar.
     * Si se supera, eliminar el más antiguo.
     */
    private const MAX_BACKUPS = 10;

    // ── Verificación de super_admin ─────────────

    private function verificarSuperAdmin(Request $request): void
    {
        // Con Spatie Laravel Permission:
        if (!$request->user()->hasRole('super_admin')) {
            abort(403, 'Solo el super administrador puede acceder a los backups del sistema.');
        }
    }

    /**
     * GET /api/v1/sistema/backups
     *
     * Lista todos los backups disponibles
     * ordenados del más reciente al más antiguo.
     */
    public function index(Request $request): JsonResponse
    {
        $this->verificarSuperAdmin($request);

        $directorioAbsoluto = storage_path('app/' . self::BACKUP_DIR);
        File::ensureDirectoryExists($directorioAbsoluto);

        $archivos = File::files($directorioAbsoluto);

        $backups = collect($archivos)
            ->map(fn($f) => $f->getPathname())
            ->filter(fn($f) => str_ends_with($f, self::EXTENSION))
            ->map(function ($ruta) {
                $nombre    = basename($ruta);
                $tamano    = File::size($ruta);
                $timestamp = File::lastModified($ruta);

                return [
                    'archivo'          => $nombre,
                    'tamano_bytes'     => $tamano,
                    'tamano_legible'   => $this->formatearTamano($tamano),
                    'creado_en'        => date('d/m/Y H:i', $timestamp),
                    'creado_timestamp' => $timestamp,
                ];
            })
            ->sortByDesc('creado_timestamp')
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Backups listados correctamente',
            'data'    => $backups,
            'meta'    => [
                'total'       => $backups->count(),
                'max_backups' => self::MAX_BACKUPS,
                'directorio'  => 'storage/app/' . self::BACKUP_DIR,
            ],
        ]);
    }

    /**
     * POST /api/v1/sistema/backups
     *
     * Genera un nuevo backup de la base de datos
     */
    public function store(Request $request): JsonResponse
    {
        $this->verificarSuperAdmin($request);

        // Verificar que pg_dump está disponible
        // Localmente en Windows, usaremos docker exec porque la DB está en Docker
        // En producción Linux, usaríamos el comando estándar
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        $pgDump = 'pg_dump';
        if ($isWindows) {
            // Docker está corriendo el postgres localmente
            $pgDump = 'docker exec congope_postgres pg_dump';
        } else {
            exec('which pg_dump 2>/dev/null', $out, $code);
            if ($code !== 0 || empty($out)) {
                $pgDump = '/usr/bin/pg_dump';
                if (!file_exists($pgDump)) {
                    $pgDump = '/usr/lib/postgresql/17/bin/pg_dump';
                }
            } else {
                $pgDump = $out[0];
            }
        }

        $dbHost     = config('database.connections.pgsql.host', '127.0.0.1');
        $dbPort     = config('database.connections.pgsql.port', '5432');
        $dbName     = config('database.connections.pgsql.database', '');
        $dbUser     = config('database.connections.pgsql.username', '');
        $dbPassword = config('database.connections.pgsql.password', '');

        if (empty($dbName) || empty($dbUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración de base de datos incompleta.',
                'errors'  => [],
            ], 500);
        }

        $directorioAbsoluto = storage_path('app/' . self::BACKUP_DIR);
        File::ensureDirectoryExists($directorioAbsoluto);

        $timestamp  = now()->format('Y-m-d_H-i-s');
        $nombre     = "congope_backup_{$timestamp}" . self::EXTENSION;
        $rutaLocal  = storage_path('app/' . self::BACKUP_DIR . '/' . $nombre);

        if ($isWindows) {
            // Cuando usamos docker exec, capturamos el stdout y lo redirigimos al archivo
            // Omitimos host/port porque se conecta directo por socket unix interno del container
            $comando = sprintf(
                'docker exec -e PGPASSWORD=%s congope_postgres pg_dump ' .
                '--username=%s ' .
                '--dbname=%s ' .
                '--format=plain ' .
                '--no-owner ' .
                '--no-acl ' .
                '--encoding=UTF8 > %s 2>&1',
                escapeshellarg($dbPassword),
                escapeshellarg($dbUser),
                escapeshellarg($dbName),
                escapeshellarg($rutaLocal)
            );
        } else {
            $comando = sprintf(
                'PGPASSWORD=%s %s ' .
                '--host=%s ' .
                '--port=%s ' .
                '--username=%s ' .
                '--dbname=%s ' .
                '--format=plain ' .
                '--no-owner ' .
                '--no-acl ' .
                '--encoding=UTF8 ' .
                '--file=%s 2>&1',
                escapeshellarg($dbPassword),
                $pgDump,
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbName),
                escapeshellarg($rutaLocal)
            );
        }

        exec($comando, $salida, $codigoSalida);

        if ($codigoSalida !== 0) {
            if (file_exists($rutaLocal)) {
                unlink($rutaLocal);
            }

            $error = implode('\n', $salida);
            \Log::error('Error en pg_dump: ' . $error);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el backup. Revisa los logs del servidor.',
                'errors'  => [],
            ], 500);
        }

        if (!file_exists($rutaLocal) || filesize($rutaLocal) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'El backup se generó pero el archivo está vacío.',
                'errors'  => [],
            ], 500);
        }

        $tamano = filesize($rutaLocal);

        \Log::info(
            'Backup generado por ' . $request->user()->email .
            ': ' . $nombre . ' (' . $this->formatearTamano($tamano) . ')'
        );

        $this->limpiarBackupsAntiguos();

        return response()->json([
            'success' => true,
            'message' => 'Backup generado correctamente',
            'data'    => [
                'archivo'        => $nombre,
                'tamano_bytes'   => $tamano,
                'tamano_legible' => $this->formatearTamano($tamano),
                'creado_en'      => now()->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /**
     * GET /api/v1/sistema/backups/{archivo}/descargar
     */
    public function descargar(Request $request, string $archivo): BinaryFileResponse
    {
        $this->verificarSuperAdmin($request);

        $nombreSanitizado = basename($archivo);

        if (!str_ends_with($nombreSanitizado, self::EXTENSION)) {
            abort(422, 'Tipo de archivo no permitido.');
        }

        $ruta = storage_path('app/' . self::BACKUP_DIR . '/' . $nombreSanitizado);

        if (!File::exists($ruta)) {
            abort(404, 'Backup no encontrado.');
        }

        return response()->download($ruta, $nombreSanitizado, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $nombreSanitizado . '"',
        ]);
    }

    /**
     * DELETE /api/v1/sistema/backups/{archivo}
     */
    public function destroy(Request $request, string $archivo): JsonResponse
    {
        $this->verificarSuperAdmin($request);

        $nombreSanitizado = basename($archivo);

        if (!str_ends_with($nombreSanitizado, self::EXTENSION)) {
            abort(422, 'Tipo de archivo no permitido.');
        }

        $ruta = storage_path('app/' . self::BACKUP_DIR . '/' . $nombreSanitizado);

        if (!File::exists($ruta)) {
            abort(404, 'Backup no encontrado.');
        }

        File::delete($ruta);

        \Log::info(
            'Backup eliminado por ' . $request->user()->email .
            ': ' . $nombreSanitizado
        );

        return response()->json([
            'success' => true,
            'message' => 'Backup eliminado correctamente',
        ]);
    }

    private function limpiarBackupsAntiguos(): void
    {
        $directorioAbsoluto = storage_path('app/' . self::BACKUP_DIR);
        if (!File::exists($directorioAbsoluto)) return;

        $archivos = collect(File::files($directorioAbsoluto))
            ->map(fn($f) => $f->getPathname())
            ->filter(fn($f) => str_ends_with($f, self::EXTENSION))
            ->sortByDesc(fn($f) => File::lastModified($f))
            ->values();

        if ($archivos->count() > self::MAX_BACKUPS) {
            $aEliminar = $archivos->slice(self::MAX_BACKUPS);
            foreach ($aEliminar as $archivo) {
                File::delete($archivo);
                \Log::info('Backup antiguo auto-eliminado: ' . basename($archivo));
            }
        }
    }

    private function formatearTamano(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $exp      = (int) floor(log($bytes, 1024));
        $exp      = min($exp, count($unidades) - 1);
        return round($bytes / (1024 ** $exp), 2) . ' ' . $unidades[$exp];
    }
}
