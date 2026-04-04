<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\HitoProyecto;

class HitoVencidoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected HitoProyecto $hito;

    /**
     * Create a new notification instance.
     */
    public function __construct(HitoProyecto $hito)
    {
        $this->hito = $hito;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Alerta: Hito de proyecto vencido')
                    ->line('El hito "' . $this->hito->nombre . '" del proyecto "' . ($this->hito->proyecto->nombre ?? 'N/A') . '" se encuentra vencido y no ha sido completado.')
                    ->line('Fecha límite: ' . $this->hito->fecha_limite->format('Y-m-d'))
                    ->action('Ver Proyecto', url('/proyectos/' . $this->hito->proyecto_id))
                    ->line('Por favor, actualice el estado del hito.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'hito_id' => $this->hito->id,
            'proyecto_id' => $this->hito->proyecto_id,
            'nombre' => $this->hito->nombre,
            'fecha_limite' => $this->hito->fecha_limite,
            'mensaje' => 'Hito vencido: ' . $this->hito->nombre,
        ];
    }
}
