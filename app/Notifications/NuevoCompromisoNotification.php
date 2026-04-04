<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\CompromisoEvento;

class NuevoCompromisoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected CompromisoEvento $compromiso;

    /**
     * Create a new notification instance.
     */
    public function __construct(CompromisoEvento $compromiso)
    {
        $this->compromiso = $compromiso;
        // Cargar el evento para obtener el título en el correo
        $this->compromiso->loadMissing('evento');
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
        $urlCompromiso = rtrim($baseUrl, '/') . '/eventos/' . $this->compromiso->evento_id;

        return (new MailMessage)
                    ->subject('Nuevo Compromiso Asignado')
                    ->greeting('¡Hola!')
                    ->line('Se te ha asignado una nueva tarea/compromiso como resultado de una mesa técnica del CONGOPE.')
                    ->line('**Evento Origen:** ' . ($this->compromiso->evento->titulo ?? 'Evento Desconocido'))
                    ->line('**Detalle del Compromiso:** ' . $this->compromiso->descripcion)
                    ->line('**Fecha Límite:** ' . \Carbon\Carbon::parse($this->compromiso->fecha_limite)->format('d/m/Y'))
                    ->action('Ver y Resolver Compromiso', $urlCompromiso)
                    ->line('Por favor asegúrate de cumplir con lo establecido y marcar la tarea como resulta oportúnamente mediante el enlace superior.');
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
            'mensaje' => 'Nuevo compromiso: ' . substr($this->compromiso->descripcion, 0, 50) . '...',
        ];
    }
}
