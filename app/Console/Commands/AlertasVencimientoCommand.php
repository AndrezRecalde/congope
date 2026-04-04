<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use App\Models\Documento;
use App\Models\HitoProyecto;
use App\Models\CompromisoEvento;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DocumentoProximoAVencerNotification;
use App\Notifications\HitoVencidoNotification;
use App\Notifications\CompromisoVencidoNotification;

#[AsCommand(
    name: 'congope:alertas-vencimiento',
    description: 'Envía alertas de documentos, hitos y compromisos próximos a vencer'
)]
class AlertasVencimientoCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Documentos próximos a vencer (30 días)
        $documentos = Documento::whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [
                today(),
                today()->addDays(30)
            ])
            ->with(['subidoPor'])
            ->get();

        // 2. Notificar por cada documento
        foreach ($documentos as $doc) {
            if ($doc->subidoPor) {
                Notification::send($doc->subidoPor, new DocumentoProximoAVencerNotification($doc));
            }
        }

        // 3. Hitos vencidos sin completar
        $hitos = HitoProyecto::pendientes()
            ->where('fecha_limite', '<', today())
            ->with(['proyecto.creadoPor'])
            ->get();

        foreach ($hitos as $hito) {
            if ($hito->proyecto?->creadoPor) {
                Notification::send(
                    $hito->proyecto->creadoPor,
                    new HitoVencidoNotification($hito)
                );
            }
        }

        // 4. Compromisos vencidos
        $compromisos = CompromisoEvento::pendientes()
            ->where('fecha_limite', '<', today())
            ->with('responsable')
            ->get();

        foreach ($compromisos as $compromiso) {
            if ($compromiso->responsable) {
                Notification::send(
                    $compromiso->responsable,
                    new CompromisoVencidoNotification($compromiso)
                );
            }
        }

        $this->info(sprintf(
            'Alertas enviadas: %d documentos, %d hitos, %d compromisos',
            $documentos->count(),
            $hitos->count(),
            $compromisos->count()
        ));

        return Command::SUCCESS;
    }
}
