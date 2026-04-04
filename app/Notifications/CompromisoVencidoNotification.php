<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CompromisoEvento;

class CompromisoVencidoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected CompromisoEvento $compromiso;

    /**
     * Create a new notification instance.
     */
    public function __construct(CompromisoEvento $compromiso)
    {
        $this->compromiso = $compromiso;
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
                    ->subject('Alerta: Compromiso de evento vencido')
                    ->line('El compromiso "' . $this->compromiso->descripcion . '" se encuentra vencido y no ha sido completado.')
                    ->line('Fecha límite: ' . $this->compromiso->fecha_limite->format('Y-m-d'))
                    ->action('Ver Evento', url('/eventos/' . $this->compromiso->evento_id))
                    ->line('Por favor, actualice el estado del compromiso.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'compromiso_id' => $this->compromiso->id,
            'evento_id' => $this->compromiso->evento_id,
            'descripcion' => $this->compromiso->descripcion,
            'fecha_limite' => $this->compromiso->fecha_limite,
            'mensaje' => 'Compromiso vencido: ' . substr($this->compromiso->descripcion, 0, 50) . '...',
        ];
    }
}
