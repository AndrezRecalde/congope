<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Documento;

class DocumentoProximoAVencerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Documento $documento;

    /**
     * Create a new notification instance.
     */
    public function __construct(Documento $documento)
    {
        $this->documento = $documento;
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
                    ->subject('Alerta: Documento próximo a vencer')
                    ->line('El documento "' . $this->documento->nombre . '" está próximo a vencer.')
                    ->line('Fecha de vencimiento: ' . $this->documento->fecha_vencimiento->format('Y-m-d'))
                    ->action('Ver Documento', url('/documentos/' . $this->documento->id))
                    ->line('Gracias por usar nuestra aplicación.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'documento_id' => $this->documento->id,
            'nombre' => $this->documento->nombre,
            'fecha_vencimiento' => $this->documento->fecha_vencimiento,
            'mensaje' => 'Documento próximo a vencer: ' . $this->documento->nombre,
        ];
    }
}
