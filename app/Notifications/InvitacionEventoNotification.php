<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Evento;

class InvitacionEventoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Evento $evento;

    /**
     * Create a new notification instance.
     */
    public function __construct(Evento $evento)
    {
        $this->evento = $evento;
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
        $baseUrl = env('FRONTEND_URL', config('app.url'));
        $urlEvento = rtrim($baseUrl, '/') . '/eventos/' . $this->evento->id;

        return (new MailMessage)
                    ->subject('Has sido invitado a un Evento: ' . $this->evento->titulo)
                    ->greeting('¡Hola!')
                    ->line('Has sido agregado como participante a un evento organizado por el CONGOPE.')
                    ->line('**Evento:** ' . $this->evento->titulo)
                    ->line('**Fecha:** ' . \Carbon\Carbon::parse($this->evento->fecha_evento)->format('d/m/Y'))
                    ->line('**Lugar:** ' . ($this->evento->lugar ?? 'Evento Virtual'))
                    ->action('Ver Evento y Confirmar Asistencia', $urlEvento)
                    ->line('Dentro de la plataforma podrás revisar los detalles y registrar tu asistencia.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'evento_id' => $this->evento->id,
            'titulo' => $this->evento->titulo,
            'fecha' => $this->evento->fecha_evento,
            'mensaje' => 'Has sido invitado al evento: ' . $this->evento->titulo,
        ];
    }
}
