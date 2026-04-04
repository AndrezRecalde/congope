<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use App\Models\RegistroAuditoria;

#[AsCommand(
    name: 'congope:limpiar-auditoria',
    description: 'Elimina registros de auditoría con más de 2 años de antigüedad'
)]
class LimpiarAuditoriaCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $eliminados = RegistroAuditoria::where(
            'created_at', '<', now()->subYears(2)
        )->delete();

        $this->info("Registros eliminados: {$eliminados}");
        
        return Command::SUCCESS;
    }
}
